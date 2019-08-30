<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/2/17
 * Time: 10:32 AM
 */

namespace ADACT\App\Models;

use ADACT\App\Models\FileManager as FM;
use phpmailerException;

/**
 * Class Process
 *
 * Process the user input data
 *
 * Tasks:
 * 1. Copy/Download necessary files (fetchFiles())
 * 2. Generate {short_name}.[m|r]aw.txt files
 * 3. Generate distance matrix (by creating SpeciesFull.txt)
 * 4. Generate phylogenic trees
 * 5. Copy them to the project directory
 *
 * NOTE: This class does not have any session dependencies!
 *
 * @package ADACT\App\Models
 */

class ProjectProcess extends PendingProjects { // is_a
    // Results from uname -s
    /** platform: macOS */
    const DARWIN = 'Darwin';
    /** platform: Linux */
    const LINUX  = 'Linux';
    /** Executable location */
    const EXEC_LOCATION = self::ROOT_DIRECTORY . '/exec';
    /** Executable list */
    const EXECS = [
        "maw" => [
            self::LINUX  => self::EXEC_LOCATION . '/maw',
            self::DARWIN => self::EXEC_LOCATION . '/maw_mac'
        ],
        "raw" => [
            self::LINUX  => self::EXEC_LOCATION . '/EAGLE',
            self::DARWIN => self::EXEC_LOCATION . '/EAGLE_mac'
        ],
        "dm"  => [
            self::LINUX  => self::EXEC_LOCATION . '/dm',
            self::DARWIN => self::EXEC_LOCATION . '/dm_mac'
        ]
    ];
    /* Error constants */
    /** No configuration file was found. */
    const E_NO_CONFIG = 'E_NO_CONFIG';
    /** Failed to fetch fasta files. */
    const E_FAILED_FETCHING_FASTA = 'E_FAILED_FETCHING_FASTA';
    /** Upload directory is not found. */
    const E_NO_UPLOAD_DIRECTORY = 'E_NO_UPLOAD_DIRECTORY';
    /** Upload directory is empty. */
    const E_EMPTY_UPLOAD_DIRECTORY = 'E_EMPTY_UPLOAD_DIRECTORY';
    /** Failed to move one or more files. */
    const E_FAILED_MOVING_FILES = 'E_FAILED_MOVING_FILES';
    /** Failed to download one or more fasta files. */
    const E_FAILED_DOWNLOADING_FASTA = 'E_FAILED_DOWNLOADING_FASTA';
    /** Failed to generate absent words. */
    const E_FAILED_GENERATING_AW = 'E_FAILED_GENERATING_AW';
    /** Failed to generate minimal absent words. */
    const E_FAILED_GENERATING_MAW = 'E_FAILED_GENERATING_MAW';
    /** RAW fasta count is zero. */
    const E_RAW_ZERO_FASTA_COUNT = 'E_RAW_ZERO_FASTA_COUNT';
    /** Failed to generate relative absent words. */
    const E_FAILED_GENERATING_RAW = 'E_FAILED_GENERATING_RAW';
    /** Failed to generate distance matrix. */
    const E_FAILED_GENERATING_DM = 'E_FAILED_GENERATING_DM';
    /** Failed to generate phylogenetic trees. */
    const E_FAILED_GENERATING_PT = 'E_FAILED_GENERATING_PT';
    /** Failed to copy files. */
    const E_FAILED_COPYING_FILES = 'E_FAILED_COPYING_FILES';
    /** Failed to copy the project files. */
    const E_FAILED_COPYING_PROJECT_FOLDER = 'E_FAILED_COPYING_PROJECT_FOLDER';
    /** Failed to copy one or more project files */
    const E_FAILED_COPYING_PROJECT_FILES = 'E_FAILED_COPYING_PROJECT_FILES';
    /** Directory not found */
    const E_DIRECTORY_NOT_FOUND = 'E_DIRECTORY_NOT_FOUND';
    /** Went to some place it shouldn't be  */
    const E_PROJECT_CANCELLED = 'E_PROJECT_CANCELLED';

    /** @var string The name of the current platform (self::DARWIN|self::LINUX) */
    private $_platform;
    /** @var ProjectConfig */
    private $_config;
    /** @var FileManager */
    private $_fm;
    /** @var FileManager the FileManager called at self::takeCare() */
    private $_tc_fm;
    /** @var Logger Logs everything */
    private $_logger;
    /** @var Executor Executes scripts */
    private $_exec;
    /** @var int|null Edit modes (self::PROJECT_INIT_FROM_INIT|self::PROJECT_INIT_FROM_AW|self::PROJECT_INIT_FROM_DM) */
    private $_edit_mode;
    /** @var string The log file location */
    private $_log_file;
    private $exec_info = [
        "AW" => [
            "cpu" => 0,
            "memory" => 0,
            "time" => 0.00
        ],
        "DM" => [
            "cpu" => 0,
            "memory" => 0,
            "time" => 0.00
        ]
    ];

    /**
     * Process constructor.
     * @param int $project_id Current project id
     * @param int $user_id Current user id
     * @throws FileException
     * @throws phpmailerException
     */
    function __construct($project_id, $user_id){
        parent::__construct($project_id, $user_id);
        $this->_project_id   = $project_id;
        $this->_fm           = new FM($project_id);
        $this->_log_file     = $this->_fm->root().'/'.FM::DEBUG_LOG;
        $this->_logger       = new Logger($this->_log_file, false);
        $config_file         = $this->_fm->get(FM::CONFIG_JSON);
        if(!file_exists($config_file)) $this->halt(self::E_NO_CONFIG);
        $this->_config       = new ProjectConfig($config_file);
        $this->_exec         = new Executor("", $this->_logger);
        $this->_platform     = exec('uname -s') == self::DARWIN ? self::DARWIN : self::LINUX;
        $this->_edit_mode    = $this->getEditMode();
        # Check if there's already exec info
        if(property_exists($this->_config, 'exec_info')){
            $this->exec_info = $this->_config->exec_info;
        }
    }

    /**
     * Init method.
     *
     * Does the necessary processes.
     * There three kind of pending processes that is to be handled with:
     * - `self::PROJECT_INIT_FROM_INIT` : Applied for new projects meaning that all the processes are to be carried away
     * - `self::PROJECT_INIT_FROM_AW` : For editing project when Absent Word Type is changed with/without other values
     * - `self::PROJECT_INIT_FROM_DM` : For editing project when only the changes are related to generating the distance matrix
     *
     * Based on the above process types, following tasks are handled:
     * 1. Fetch files (only for `self::PROJECT_INIT_FROM_INIT`)
     * 2. Generate maw/raw files (not applicable for `self::PROJECT_INIT_FROM_DM`)
     * 3. Generate distance matrix
     * 4. Generate phylogenetic trees
     * 5. Copy/Move all the items to their respective directories
     * 6. Send an email to the user on success
     * @throws FileException
     * @throws phpmailerException
     */
    function init(){
        // Log project info
        $this->_log("Project: {$this->_config->project_name} ({$this->_project_id})", Logger::BG_RED.Logger::BOLD.Logger::WHITE);
        if($this->_edit_mode === null) return;  // Since PHP doesn't distinguish between null and 0 in a switch statement
        // Start process time
        $this->_set_start();
        switch ($this->_edit_mode){             // Deliberate fallthrough: DO NOT add any break statement after each case, DO NOT alternate the cases
            /** @noinspection PhpMissingBreakStatementInspection */
            case self::EM_INIT_FROM_INIT:
                $this->_log("@ProjectProcess::PROJECT_INIT_FROM_INIT", Logger::BOLD);
                // 1. Fetch files
                $this->_status = self::PROJECT_FETCHING_FASTA;
                $this->_action('fetchFiles', self::E_FAILED_FETCHING_FASTA);
            /** @noinspection PhpMissingBreakStatementInspection */
            case self::EM_INIT_FROM_AW:
                $this->_log("@ProjectProcess::PROJECT_INIT_FROM_AW", Logger::BOLD);
                // 2. Generate {short_name}.[m|r]aw.txt files
                $this->_status = self::PROJECT_FINDING_AW;
                $this->_action('generateAW', self::E_FAILED_GENERATING_AW);
            case self::EM_INIT_FROM_DM:
                $this->_log("@ProjectProcess::PROJECT_INIT_FROM_DM", Logger::BOLD);
                // 3. Generate distance matrix (by creating SpeciesFull.txt)
                $this->_status = self::PROJECT_GENERATE_DM;
                $this->_action('generate_distance_matrix', self::E_FAILED_GENERATING_DM);
                // 4. Generate phylogenetic trees
                $this->_status = self::PROJECT_GENERATE_PT;
                $this->_action('generate_phylogenetic_trees',self::E_FAILED_GENERATING_PT);
                // 5. Copy them to the project directory
                $this->_status = self::PROJECT_TAKE_CARE;
                $this->_action('takeCare', self::E_FAILED_COPYING_FILES);
        }
        // If it comes at this stage, the process was successful.
//        $this->remove(); // Remove from the pending list FIXME Do this in a cron job to clean things up
        $this->setStatus(self::PROJECT_SUCCESS);
        $this->_log('The project was processed successfully.', Logger::BG_RED.Logger::BOLD.Logger::WHITE);
        // Store process terminating time
        $this->_set_end();
        // Send mail
        $this->send_mail();
    }

    public function __destruct(){
        // Final flash, since there's no way to call the destruct
        $this->_logger->flush();
        $this->_logger = null;
        // Also, add the debug log to the process.log
        // provided the DEBUG mode is enabled.
        if(self::DEBUG_MODE && file_exists($this->_log_file)) file_put_contents(__DIR__ . '/../../logs/process.log', file_get_contents($this->_log_file), FILE_APPEND);
        // Delete the debug log if the process was successful
        if($this->_status !== self::PROJECT_FAILURE) unlink($this->_log_file);
    }

    /* Private functions */

    /**
     * Done all the actions
     *
     * @param callable $callback Callback function to be called
     * @param string   $failureMessage Failure message
     * @return bool
     * @throws FileException
     * @throws phpmailerException
     */
    private function _action($callback, $failureMessage){
        // Halt execution if project is cancelled
        if($this->isCancelled()) $this->halt(self::E_PROJECT_CANCELLED);
        // Log to debug log
        $this->_log("=> ". self::STATUS[$this->_status], Logger::INVERT);
        // Set current status
        $this->setStatus($this->_status);
        // Run the task
        $result = call_user_func([$this, $callback]);
        // Check the task
        if(!$result) $this->halt($failureMessage);
        // Return result
        return $result;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Move things where they belong
     *
     * @param bool $moveOnly
     * @return bool
     * @throws FileException
     * @throws phpmailerException
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function takeCare($moveOnly = false){
        // Save modified config file
        $this->_config->setConfig('exec_info', $this->exec_info);
        $this->_config->save();
        $fm  = new FM($this->_project_id, Project::PT_NEW);
        // Secondary fm
        $this->_tc_fm = $fm;
        $project_dir = self::PROJECT_DIRECTORY;
        // Move to the main project directory if necessary
        // But before, flush the log
        if($this->_fm->root() !== $project_dir . '/' . $this->_project_id){
            $this->_logger->flush();
            if($this->_exec->new(['mv', $this->_quotify($this->_fm->root()), $this->_quotify($project_dir)], $this->_logger)
                    ->execute()->returns() != 0){
                $this->halt(self::E_FAILED_COPYING_PROJECT_FOLDER);
                return false;
            }
            // Reset logger since debug.log is also moved
            $this->_log_file = $this->_tc_fm->get(FM::DEBUG_LOG);
            $this->_logger = new Logger($this->_log_file, false);
        }

        if($moveOnly){ // Move only is requested
            $fm->store(FM::CONFIG_JSON, $this->_config->getConfigJSON(), FM::STORE_STRING);
            return true;
        }
        // CD to root directory
        $fm->cd($fm->root());
        // Move required files to Project/{project_dir}
        if($this->_move(FM::SPECIES_RELATION)
            AND $this->_move(FM::SPECIES_RELATION_JSON)
            AND $this->_move(FM::DISTANCE_MATRIX)
            AND $this->_move(FM::DISTANT_MATRIX_FORMATTED)
            AND $this->_move(FM::NEIGHBOUR_TREE)
            AND $this->_move(FM::UPGMA_TREE)
            // Store config.json
            AND $fm->store(FM::CONFIG_JSON, $this->_config->getConfigJSON(), FM::STORE_STRING)
        ){
            return true;
        }else{
            $this->halt(self::E_FAILED_COPYING_PROJECT_FILES);
            return false;
        }
    }

    /**
     * @param bool $isSuccess Which message to show up
     * @param int $error_constant
     * @return bool
     * @throws phpmailerException
     */
    private function send_mail($isSuccess = true, $error_constant = null){
        $user_info    = (new User())->get_info($this->_user_id);
        $project_link = self::WEB_ADDRESS . '/projects/' . $this->_project_id;
        $subject  = $isSuccess ? 'Project has been executed successfully' : 'Project is failed to execute';
        $view_btn = $isSuccess ? Emailer::button("View Results", $project_link) : Emailer::button("View Configurations", $project_link);
        $table_th = 'style="background: burlywood;padding: 5px 10px;"';
        $table_td = 'style="background: lavender;padding: 5px 10px;"';
        $body     = $isSuccess ?
            <<< EOF
<p>Congratulations!</p>
<p>The project <strong>{$this->_config->project_name}</strong> has been executed successfully.</p>
<div>{$view_btn}</div>
EOF
            : <<< EOF
<p>The project <strong>{$this->_config->project_name}</strong> is failed to execute.</p>
<div>{$view_btn}</div>
<p>If you need further help, contact us with the following information:</p>
<table style="text-align: left;">
<tbody>
<tr>
    <th {$table_th}>Project Name</th>
    <td {$table_td}>{$this->_config->project_name}</td>
</tr>
<tr>
    <th {$table_th}>Project ID</th>
    <td {$table_td}>{$this->_project_id}</td>
</tr>
<tr>
    <th {$table_th}>Error Code</th>
    <td {$table_td}>{$error_constant}</td>
</tr>
</tbody>
</table>

EOF;
        return self::formatted_email($user_info['name'], $user_info['email'], $subject, $body);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Copy/Download necessary files
     *
     * @return bool
     * @throws FileException
     * @throws phpmailerException
     */
    private function fetchFiles(){
        if($this->_config->type == Project::INPUT_TYPE_FILE){
            $this->_log("Input type: file upload", Logger::BOLD);
            return $this->move_uploaded_files();
        }else /* if($this->_config->type == Project::INPUT_TYPE_ACCN_GIN) */{
            $this->_log("Input type: accn/gin", Logger::BOLD);
            return $this->download_fasta($this->_config->sequence_type);
        }
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @return bool
     * @throws FileException
     * @throws phpmailerException
     */
    private function generateAW(){
        if($this->_config->aw_type == 'maw'){
            return $this->generate_maw();
        }else /* if($aw_type == 'raw')*/ {
            return $this->generate_raw();
        }
    }

    const FONT_SIZE  = 10;
    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @return bool
     * @throws FileException
     * @throws phpmailerException
     */
    private function generate_phylogenetic_trees(){
        try{
            $tree  = new Tree($this->_project_id, Tree::GENERAL);
            $nj    = $tree->getNewickFormat(Tree::NJ);
            $upgma = $tree->getNewickFormat(Tree::UPGMA);
            return $this->saveTree($nj, $this->_fm->generated() . '/' . FM::NEIGHBOUR_TREE)
                AND $this->saveTree($upgma, $this->_fm->generated() . '/' . FM::UPGMA_TREE);
        }catch (FileException $e){
            $this->_log($e->getMessage(), Logger::RED);
            $this->halt(self::E_FAILED_GENERATING_PT);
            return false;
        }
    }

    /**
     * @param string $tree Tree in newick format
     * @param string $file Filename to save
     * @return bool
     */
    private function saveTree($tree, $file){
        return file_put_contents($file, $tree) !== false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @return bool
     */
    private function generate_distance_matrix(){
        // set target directory
        $target = $this->_fm->generated();
        // Create SpeciesFull.txt
        $this->_create_species_full();
        // Run Distance Matrix Generator
        $this->_config->aw_type = strtoupper($this->_config->aw_type);
        $this->_exec->new([
            $this->_quotify(self::EXECS['dm'][$this->_platform]),
            $this->_config->aw_type,
            $this->_config->dissimilarity_index,
            $this->_quotify($target),
            $this->_quotify($target)
        ], $this->_logger)->execute();
        if($this->_exec->returns() === 0){
            $this->exec_info['DM'] = [
                'cpu' => $this->_exec->get_cpu(),
                'memory' => $this->_exec->get_memory(),
                'time' => $this->_exec->get_time()
            ];
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws FileException
     * @throws phpmailerException
     */
    private function generate_maw(){
        $sequence_type = ($this->_config->sequence_type == 'nucleotide') ? 'DNA' : 'PROT';
        // Generate {short_name}.maw.txt from the input fasta files
        foreach($this->get_files($this->_fm->original()) as $file){
            // Filename: {species_name}.maw.txt
            $output_file = $this->_fm->generated() . '/' . basename($file, '.fasta') . '.maw.txt';
            $this->_exec->new([
                $this->_quotify(self::EXECS['maw'][$this->_platform]),
                '-a', $sequence_type,
                '-i', $this->_quotify($file),
                '-o', $this->_quotify($output_file),
                '-k', $this->_config->kmer["min"],
                '-K', $this->_config->kmer["max"],
                ($this->_config->inversion ? ' -r 1' : '')
            ], $this->_logger)->execute();
            if($this->_exec->returns() !== 0){
                $this->_log("Generating maw failed for " . $file, Logger::RED);
                $this->halt(self::E_FAILED_GENERATING_MAW);
                return false;
            } else {
                if($this->exec_info['AW']['cpu'] < $this->_exec->get_cpu()) $this->exec_info['AW']['cpu'] = $this->_exec->get_cpu();
                if($this->exec_info['AW']['memory'] < $this->_exec->get_memory()) $this->exec_info['AW']['memory'] += $this->_exec->get_memory();
                $this->exec_info['AW']['time'] += $this->_exec->get_time();
            }
        }
        return true;
    }

    private $_ref_index = 0;
    private $_files;
    private $_fasta_count = 0;

    /**
     * generate_raw method
     *
     * generates {species_name}.raw.txt by compare it to all the other ones
     *
     * @return bool
     * @throws FileException
     * @throws phpmailerException
     */
     private function generate_raw(){
         // Initial tasks
         if($this->_ref_index == 0){
             $this->_files = $this->get_files($this->_fm->original());
             $this->_fasta_count = count($this->_files);
         }
         // 1. Copy $this->files to $modified_files to prevent any data loss
         $modified_files = $this->_files;
         // 2. Set a reference file ($ref_file)
         if($this->_ref_index < $this->_fasta_count){
             // Set reference file according to the
             $ref_file = $modified_files[$this->_ref_index];
             // Delete the reference file name from the $modified_files
             unset($modified_files[$this->_ref_index]);
         }else{
             if($this->_fasta_count > 0) return true;
             else{
                 $this->halt(self::E_RAW_ZERO_FASTA_COUNT);
                 return false;
             }
         };
         // 3. Get the species_name from the reference file
         $ref_file_name = basename($ref_file, '.fasta');
         // 4. Create a /tmp/Projects/{project_id}/Files/generated/{species_name} directory
         $ref_file_dir = $this->_fm->generated() . '/' . $ref_file_name;
         if(!file_exists($ref_file_dir)) mkdir($ref_file_dir);
         // 5. Generate {species_name}.raw.txt in the /tmp/Projects/{project_id}/Files/original directory
         foreach($modified_files as $file){
             if($this->_exec->new([
                 $this->_quotify(self::EXECS['raw'][$this->_platform]),
                 '-min', $this->_config->kmer["min"],
                 '-max', $this->_config->kmer["max"],
                 ($this->_config->inversion ? '-i' : ''),
                 '-r', $this->_quotify($ref_file),
                 $this->_quotify($file)
             ], $this->_logger)->execute()->returns() != 0){
                 $this->halt(self::E_FAILED_GENERATING_RAW);
                 return false;
             } else {
                 if($this->exec_info['AW']['cpu'] < $this->_exec->get_cpu()) $this->exec_info['AW']['cpu'] = $this->_exec->get_cpu();
                 if($this->exec_info['AW']['memory'] < $this->_exec->get_memory()) $this->exec_info['AW']['memory'] += $this->_exec->get_memory();
                 $this->exec_info['AW']['time'] += $this->_exec->get_time();
             }
             // Move the *.raw.txt files to the /tmp/Projects/{project_id}/Files/generated/raw/{species_name} directory
             $this->_exec->new([
                 'mv', "\"{$this->_fm->original()}\"/*.raw.txt", $this->_quotify($ref_file_dir)
             ], $this->_logger)->execute();
         }
         // increment by one
         ++$this->_ref_index;
         return $this->generate_raw();
    }


    /**
     * @return bool
     * @throws FileException
     * @throws phpmailerException
     */
    private function move_uploaded_files(){
        $uploader = new FileUploader();
        // Get the temporary upload directory
        $upload_directory = $uploader->getFromID($this->_config->file_id);
        // Cancel project if no valid directory found: this is highly unlikely to be happened
        if($upload_directory === false){
            $this->halt(self::E_NO_UPLOAD_DIRECTORY);
            return false;
        }

        // Get file names
        $files = $this->get_files($upload_directory);
        if(count($files) <= 0){
            $this->halt(self::E_EMPTY_UPLOAD_DIRECTORY);
            return false;
        }
        $f_names = $this->full_name_to_short_name($this->_config->data);
        // cd to the original directory
        if(!$this->_fm->cd($this->_fm->original())) $this->_fm->create();
        // Move file to the pwd
        foreach ($files as $file){
            if(!$this->_fm->store($f_names[basename($file, '.fasta')] . '.fasta', $file, FM::STORE_MOVE)){
                $this->_log("Moving {$file} failed!", Logger::RED);
                $this->halt(self::E_FAILED_MOVING_FILES);
                return false;
            }else{
                $this->_log("File: {$file}");
            }
        }
        // Delete the directory where the extracted files are previously stored, along with session
        $this->_exec->new(['rm', '-Rf', $this->_quotify($upload_directory)], $this->_logger)->execute();
        $uploader->removeByID($this->_config->file_id);
        return true;
    }

    /**
     * download_fasta
     *
     * Download FASTA from NCBI DB
     *
     * @param string $sequence_type Which type of DB should be used (protein|nucleotide)
     * @return bool
     * @throws FileException
     * @throws phpmailerException
     */
    private function download_fasta($sequence_type){
        $data        = $this->_config->data;
        $database    = $sequence_type == 'protein' ? 'protein' : 'nuccore';
        $gin_count   = count($data);

        if(!$this->_fm->cd($this->_fm->original())) $this->_fm->create();
        for($i = 0; $i < $gin_count; ++$i){
            if(!$this->_fm->store($data[$i]['short_name'] . '.fasta', "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db={$database}&id={$data[$i]['gin']}&rettype=fasta&retmode=text", FM::STORE_DOWNLOAD)){
                $this->_log("{$data[$i]['gin']} couldn't be downloaded!");
                $this->halt(self::E_FAILED_DOWNLOADING_FASTA);
                return false;
            }else{
                $this->_log("File: {$data[$i]['short_name']}.fasta");
            }
        }
        return true;
    }

    /* Other Methods */
    /**
     * full_name_to_short_name method
     *
     * (If anyone reading this, please rename it to an appropriate name
     * if you've found any. I cannot think of any appropriate name!)
     *
     * Full name & short name can have at most 40 characters
     *
     * @param array $data
     * @return array An associative array of title/id (full name) as key and short name as value
     */
    private function full_name_to_short_name($data){
        $assoc  = [];
        $c_keys = count($data);
        for($i = 0; $i < $c_keys; ++$i){
            $assoc[($this->_config->type == Project::INPUT_TYPE_ACCN_GIN ? $data[$i]['title'] : $data[$i]['id'])] = $data[$i]['short_name'];
        }
        return $assoc;
    }

    /**
     * Halt the project process/executions.
     *
     * There are two situations (higher priorities on the top):
     * 1. Project has been cancelled: cancel = TRUE in pending_projects table
     * 2. Project has failed: cancel = FALSE and status_code = self::PROJECT_FAILURE in pending_projects table
     *
     * Again, cancelling a project has two situations:
     * - The project is new : Delete input files with a message
     * - The project is being edited : Restore previous version if possible
     *
     * Similarly, a failed project has two situations:
     * - The project is new : Store everything
     * - The project is being edited : Restore previous version if possible with the newest debug.log
     *
     * In case of a failed attempt, email the user saying the error code so that the user can
     * contact with the institution for further information.
     *
     * @param string|null $error_constant Processing-related error constants of the same class
     * @throws FileException
     * @throws phpmailerException
     */
    private function halt($error_constant = null){
        if($error_constant !== null) $this->_log("ERROR: {$error_constant}", Logger::BOLD.Logger::BG_RED.Logger::WHITE);
        if($this->isCancelled()){
            // project has been cancelled
            // perform delete with a message
            $this->_cancel_action();
        }else{
            // project has failed but not cancelled
            // Store everything including the debug log
            $this->takeCare(true);
            // Send email
            $this->send_mail(false, $error_constant);
            // Project is also failed
            $this->_status = self::PROJECT_FAILURE;
            $this->setStatus($this->_status);
        }
        $this->_set_end(); // Set termination time
        exit(); // TODO: Should I try and catch error? This may not be a good idea.
    }

    /**
     * Actions due to cancelling a project
     */
    private function _cancel_action(){
        $r_stat = (new Remover($this->_project_id, $this->_user_id))->innocentlyRemove();
        if($r_stat === Remover::R_DELETE_INPUT){ // Reverted
            $this->_log("Reverted back to the previous result.", Logger::GREEN);
        }else{ // Couldn't revert back
            $this->_log("The project files are deleted.", Logger::RED);
        }
    }

    /**
     * @param $dir
     * @return array
     * @throws FileException
     * @throws phpmailerException
     */
    private function get_files($dir){
        if(!$this->_fm->cd($dir, true)){
            $this->halt(self::E_DIRECTORY_NOT_FOUND);
            return [];
        }
        return $this->_fm->getAll(true);
    }

    /**
     * Creates SpeciesFull.txt inside the generated directory,
     * necessary when creating Distance Matrix.
     */
    private function _create_species_full(){
        $species = [];
        foreach($this->_config->data as $datum) array_push($species, $datum['short_name']);
        array_push($species, '');    // Won't work without this!!! (because dm binary file uses gets() to read lines!)
        // Export file
        file_put_contents($this->_fm->generated() . '/SpeciesFull.txt', implode("\n", $species));
    }

    /**
     * Move files to the project root
     *
     * @param string $filename Only the filename, use FileManager constants
     * @return bool
     * @throws FileException
     */
    private function _move($filename){
        return $this->_tc_fm->store($filename, $this->_tc_fm->generated() . '/' . $filename, FM::STORE_MOVE);
    }

    /**
     * Set project start time
     * @return bool
     */
    private function _set_start(){
        if($stmt = $this->mysqli->prepare('UPDATE projects SET project_started = NOW() WHERE project_id = ?')){
            $stmt->bind_param('i', $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1) return true;
        }
        return false;
    }

    /**
     * Set project end time
     * @return bool
     */
    private function _set_end(){
        if($stmt = $this->mysqli->prepare('UPDATE projects SET project_finished = NOW() WHERE project_id = ?')){
            $stmt->bind_param('i', $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1) return true;
        }
        return false;
    }

    /**
     * Log messages
     *
     * @param string $message The message to be logged
     * @param null   $formats Format related constant(s) from Logger class
     */
    private function _log($message, $formats = null){
        $this->_logger->log($message, $formats);
    }

    private function _quotify($string){
        return "\"{$string}\"";
    }
}