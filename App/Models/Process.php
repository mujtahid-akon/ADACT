<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/2/17
 * Time: 10:32 AM
 */

namespace AWorDS\App\Models;


use AWorDS\App\Constants;

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
 * @package AWorDS\App\Models
 */

class Process extends Model{
    // Results from uname -s
    const DARWIN = 'Darwin';
    const LINUX  = 'Linux';
    // Executable location
    const EXEC_LOCATION = self::ROOT_DIRECTORY . '/exec';
    // Executable list
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
        ],
        "phylogenetic_tree" => 'java -cp ' . self::EXEC_LOCATION . ' Match7'
    ];
    private $_platform;
    /**
     * @var ProjectConfig
     */
    private $_config;
    /**
     * @var FileManager
     */
    private $_fm;
    /**
     * @var FileManager the FileManager called at self::takeCare()
     */
    private $_tc_fm;
    private $_project_id;
    private $_user_id;
    private $_pending_process;

    /**
     * Process constructor.
     * @param int        $project_id   Current project id
     * @param int        $user_id      Current user id
     */
    function __construct($project_id, $user_id){
        parent::__construct();
        $this->_project_id   = $project_id;
        $this->_user_id      = $user_id;
        $this->_fm           = new FileManager($project_id);
        $config_file         = $this->_fm->get(FileManager::CONFIG_JSON);
        if(!file_exists($config_file)){
            $this->_log("No config.json file found at {$this->_fm->pwd()}");
            exit();
        }
        // Config file
        $this->_config       = new ProjectConfig($config_file); //json_decode(file_get_contents($config_file), true);
        // get platform
        $this->_platform     = exec('uname -s') == self::DARWIN ? self::DARWIN : self::LINUX;
        // Pending Process
        $this->_pending_process = new PendingProjects($project_id, $user_id);
    }

    function init(){
        // 1. Fetch files
        $this->_pending_process->status(PendingProjects::PROJECT_FETCHING_FASTA);
        if(!$this->fetchFiles()){
            $this->_pending_process->status(PendingProjects::PROJECT_FAILURE);
            $this->halt('Fetching files failed!');
        }
        // 2. Generate {short_name}.[m|r]aw.txt files
        $this->_pending_process->status(PendingProjects::PROJECT_FINDING_AW);
        if(!$this->generateAW()){
            $this->_pending_process->status(PendingProjects::PROJECT_FAILURE);
            $this->halt('Generating absent words failed!');
        }
        // 3. Generate distance matrix (by creating SpeciesFull.txt)
        $this->_pending_process->status(PendingProjects::PROJECT_GENERATE_DM);
        if(!$this->generate_distance_matrix()){
            $this->_pending_process->status(PendingProjects::PROJECT_FAILURE);
            $this->halt('Generating distance matrix failed!');
        }
        // 4. Generate phylogenetic trees: FIXME
//        $this->_pending_process->status(PendingProjects::PROJECT_GENERATE_PT);
//        if(!$this->generate_phylogenetic_trees()){
//            $this->_pending_process->status(PendingProjects::PROJECT_FAILURE);
//            $this->halt('Generating Phylogenetic trees failed!');
//        }
        // 5. Copy them to the project directory
        $this->_pending_process->status(PendingProjects::PROJECT_TAKE_CARE);
        if(!$this->takeCare()){
            $this->_pending_process->status(PendingProjects::PROJECT_FAILURE);
            $this->halt('Copying files failed!');
        }
        // Success
        $this->_pending_process->remove();
        // Send mail
        $this->send_mail();
        $this->_pending_process->status(PendingProjects::PROJECT_SUCCESS);
    }

    /**
     * Private functions
     */

    /**
     * @return bool
     */
    function takeCare(){
        $fm  = new FileManager($this->_project_id, Project::NEW_PROJECT);
        $this->_tc_fm = $fm;
        $project_dir = self::PROJECT_DIRECTORY;
//        error_log($project_dir);
//        error_log($fm->generated());
        // Move /tmp/Projects/{project_id}/ to /Projects/{project_id}/
        passthru("mv \"{$this->_fm->root()}\" \"{$project_dir}\"");
        // CD to root directory
        $fm->cd($fm->root());
        // Move required files to Project/{project_dir}
        $this->_move($fm::SPECIES_RELATION);
        $this->_move($fm::SPECIES_RELATION_JSON);
        $this->_move($fm::DISTANT_MATRIX);
        $this->_move($fm::DISTANT_MATRIX_FORMATTED);
        $this->_move($fm::NEIGHBOUR_TREE);
        $this->_move($fm::UPGMA_TREE);
        // Store config.json
        $fm->store($fm::CONFIG_JSON, $this->_config->getConfigJSON(), $fm::STORE_STRING);
        return true;
    }

    private function send_mail(){
        $user_info = (new User())->get_info($this->_user_id);
        $project_link = self::WEB_ADDRESS . '/projects/' . $this->_project_id;
        $subject = 'Project Execution Completed';
        $body    = <<< EOF
<p>Congratulations!</p>
<p>
    The project named '{$this->_config->project_name}' is completed successfully.
    Please check notifications or click the link below to view the results.<br />
    <a href="{$project_link}">{$project_link}</a>
</p>

EOF;

        return self::formatted_email($user_info['name'], $user_info['email'], $subject, $body);
    }

    /**
     * Copy/Download necessary files
     *
     * @return bool
     */
    private function fetchFiles(){
        if($this->_config->type == Constants::PROJECT_TYPE_FILE){
            return $this->move_uploaded_files();
        }else /* if($this->_config->type == Constants::PROJECT_TYPE_ACCN_GIN) */{
            return $this->download_fasta($this->_config->sequence_type);
        }
    }

    /**
     * @return bool
     */
    private function generateAW(){
        if($this->_config->aw_type == 'maw'){
            return $this->generate_maw();
        }else /* if($aw_type == 'raw')*/ {
            return $this->generate_raw();
        }
    }

    private function generate_phylogenetic_trees(){
        exec(self::EXECS['phylogenetic_tree'] . ' "'. $this->_fm->generated() .'/"', $output, $return);
        $this->_log(implode("\n", $output));
        return $return === 0 ? true : false;
    }

    private function generate_distance_matrix(){
        // set target directory
        $target = $this->_fm->generated();
        // Create SpeciesFull.txt
        $this->gen_species_full();
        // Run Distance Matrix Generator
        $this->_config->aw_type = strtoupper($this->_config->aw_type);
        exec(self::EXECS['dm'][$this->_platform] . " {$this->_config->aw_type} {$this->_config->dissimilarity_index} {$target} {$target}", $output, $return);
        $this->_log(implode("\n", $output)); unset($output);
        return $return === 0 ? true : false;
    }


    /**
     * @return bool
     */
    private function generate_maw(){
        $sequence_type = ($this->_config->sequence_type == 'nucleotide') ? 'DNA' : 'PROT';
        // Generate {short_name}.maw.txt from the input fasta files
        foreach($this->get_files($this->_fm->original()) as $file){
            // Filename: {species_name}.maw.txt
            $output_file = $this->_fm->generated() . '/' . basename($file, '.fasta') . '.maw.txt';
            exec(self::EXECS['maw'][$this->_platform] . " -a {$sequence_type} -i '{$file}' -o '{$output_file}' -k {$this->_config->kmer["min"]} -K {$this->_config->kmer["max"]}" . ($this->_config->inversion ? ' -r 1' : ''), $output, $return);
            $this->_log(implode("\n", $output));
            if($return !== 0){
                $this->halt("Generating maw failed for " . $file);
                return false;
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
                 $this->halt("Generating raw failed!");
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
             exec(self::EXECS['raw'][$this->_platform] . " -min {$this->_config->kmer["min"]} -max {$this->_config->kmer["max"]}" . ($this->_config->inversion ? ' -i' : '') . " -r '{$ref_file}' '{$file}'", $output);
             $this->_log(implode("\n", $output));
             // Move the *.raw.txt files to the /tmp/Projects/{project_id}/Files/generated/raw/{species_name} directory
             passthru("mv '{$this->_fm->original()}'/*.raw.txt '{$ref_file_dir}'");
         }
         // increment by one
         ++$this->_ref_index;
         return $this->generate_raw();
    }


    /**
     * @return bool
     */
    private function move_uploaded_files(){
        $uploader = new FileUploader();
        // Get the temporary upload directory
        $upload_directory = $uploader->getFromID($this->_config->file_id);
        // Cancel project if no valid directory found: this is highly unlikely to be happened
        if($upload_directory === false){
            $this->halt("Upload directory cannot be found!");
            return false;
        }

        // Get file names
        $files = $this->get_files($upload_directory);
        if(count($files) <= 0){
            $this->halt("Upload directory is empty!");
            return false;
        }

        $f_names = $this->full_name_to_short_name($this->_config->data);

        // cd to the original directory
        if(!$this->_fm->cd($this->_fm->original())) $this->_fm->create();
        // Move file to the pwd
        foreach ($files as $file){
            if(!$this->_fm->store($f_names[basename($file, '.fasta')] . '.fasta', $file, $this->_fm::STORE_MOVE)){
                $this->halt("Moving {$file} failed!");
                return false;
            }
        }
        // Delete the directory where the extracted files are previously stored, along with session
        exec("rm -Rf {$upload_directory}");
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
     */
    private function download_fasta($sequence_type){
        $data        = $this->_config->data;
        $database    = $sequence_type == 'protein' ? 'protein' : 'nuccore';
        $gin_count   = count($data);

        if(!$this->_fm->cd($this->_fm->original())) $this->_fm->create();
        for($i = 0; $i < $gin_count; ++$i){
            if(!$this->_fm->store($data[$i]['short_name'] . '.fasta', "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db={$database}&id={$data[$i]['gin']}&rettype=fasta&retmode=text", $this->_fm::STORE_DOWNLOAD)){
                $this->halt("{$data[$i]['gin']} couldn't be downloaded!");
                return false;
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
            $assoc[($this->_config->type == Constants::PROJECT_TYPE_ACCN_GIN ? $data[$i]['title'] : $data[$i]['id'])] = $data[$i]['short_name'];
        }
        return $assoc;
    }

    private function halt($message = null){
        if($message !== null) $this->_log($message);
        (new Project())->delete($this->_project_id, $this->_user_id) === 0 ? true : false;
        exit();
    }

    private function get_files($dir){
        if(!$this->_fm->cd($dir, true)){
            $this->halt("{$dir} doesn't exist!");
            return [];
        }
        return $this->_fm->getAll();
    }

    /**
     * gen_species_full method.
     *
     * Generates SpeciesFull.txt
     */
    private function gen_species_full(){
        $species = [];
        $data    = $this->_config->data;
        foreach($data as $datum){
            array_push($species, $datum['short_name']);
        }
        array_push($species, '');    // Won't work without this!!! (because dm used gets() to read lines)
        // Export file
        file_put_contents($this->_fm->generated() . '/SpeciesFull.txt', implode("\n", $species));
    }

    /**
     * @param string $filename Only the filename
     * @return bool
     */
    private function _move($filename){
        return $this->_tc_fm->store($filename, $this->_tc_fm->generated() . '/' . $filename, $this->_tc_fm::STORE_MOVE);
    }

    private function _log($message){
        file_put_contents(__DIR__ . '/../../logs/process.log', $message . "\n", FILE_APPEND);
    }
}