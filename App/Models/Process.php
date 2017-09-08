<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/2/17
 * Time: 10:32 AM
 */

namespace AWorDS\App\Models;


use AWorDS\App\Constants;
use AWorDS\Config;

/**
 * Class Process
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

class Process extends Model
{
    // Results from uname -a
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
        "match7" => 'java -cp ' . self::EXEC_LOCATION . ' Match7'
    ];
    private $_platform;
    /**
     * @var array
     */
    private $_config;
    /**
     * @var Directories
     */
    private $_dir;
    private $_project_id;
    private $_user_id;
    private $_uploaded_files;
    private $_pending_process;

    /**
     * Process constructor.
     * @param int        $project_id   Current project id
     * @param int        $user_id      Current user id
     * @param array|null $uploaded_files Array if project type is file and null if accn_gin (Array should be $_SESSION['upload_info'])
     */
    function __construct($project_id, $user_id, $uploaded_files = null){
        parent::__construct();
        $this->_project_id   = $project_id;
        $this->_user_id      = $user_id;
        $this->_uploaded_files = $uploaded_files;
        $this->_dir          = new Directories($project_id);
        $config_file         = $this->_dir->get(Constants::CONFIG_JSON);
        if(!file_exists($config_file)){
            error_log("No config.json file found at {$this->_dir->pwd()}");
            exit();
        }
        // Config file
        $this->_config       = json_decode(file_get_contents($config_file), true);
        // get platform
        $this->_platform     = exec('uname -s') == self::DARWIN ? self::DARWIN : self::LINUX;
        // Pending Process
        $this->_pending_process = new PendingProjects($project_id);
    }

    function init(){
        $status = true;
        // 1. Fetch files
        $this->_pending_process->status(PendingProjects::PROJECT_FETCHING_FASTA);
        if(!$this->fetchFiles()){
            $this->_pending_process->status(PendingProjects::PROJECT_FAILURE);
            $status = false;
        }
        // 2. Generate {short_name}.[m|r]aw.txt files
        $this->_pending_process->status(PendingProjects::PROJECT_FINDING_AW);
        if(!$this->generateAW()){
            $this->_pending_process->status(PendingProjects::PROJECT_FAILURE);
            $status = false;
        }
        // 3. Generate distance matrix (by creating SpeciesFull.txt)
        $this->_pending_process->status(PendingProjects::PROJECT_GENERATE_DM);
        if(!$this->generate_distance_matrix()){
            $this->_pending_process->status(PendingProjects::PROJECT_FAILURE);
            $status = false;
        }
        // 4. Generate phylogenic trees
        $this->_pending_process->status(PendingProjects::PROJECT_GENERATE_PT);
        if(!$this->generate_phylogenic_trees()){
            $this->_pending_process->status(PendingProjects::PROJECT_FAILURE);
            $status = false;
        }
        // 5. Copy them to the project directory
        $this->_pending_process->status(PendingProjects::PROJECT_TAKE_CARE);
        if(!$this->takeCare()){
            $this->_pending_process->status(PendingProjects::PROJECT_FAILURE);
            $status = false;
        }
        // Success
        $this->_pending_process->remove($this->_project_id);
        if($status == true){
            // Send mail
            $this->send_mail();
            $this->_pending_process->status(PendingProjects::PROJECT_SUCCESS);
        }

    }

    /**
     * Private functions
     */

    /**
     * @return bool
     */
    function takeCare(){
        $project_dir = self::PROJECT_DIRECTORY . '/' . $this->_project_id;
        error_log($project_dir);
        // 1. Move /tmp/Projects/{project_id}/ to /Projects/{project_id}/
        passthru("mv \"{$this->_dir->project_dir()}\" \"{$project_dir}/\"");
        // Notice: the trailing slash
        $files_dir = $project_dir . '/Files/generated/';
        error_log($files_dir);
        // 2. Copy required files to Project/{project_idr}
        // 2.1 SpeciesRelation.txt
        if(file_exists($files_dir . Constants::SPECIES_RELATION))
            copy($files_dir . Constants::SPECIES_RELATION, $project_dir . '/' . Constants::SPECIES_RELATION);
        // 2.2 Output.txt
        if(file_exists($files_dir . Constants::DISTANT_MATRIX))
            copy($files_dir . Constants::DISTANT_MATRIX, $project_dir . '/' . Constants::DISTANT_MATRIX);
        // 2.3 Neighbour tree.jpg
        if(file_exists($files_dir . Constants::NEIGHBOUR_TREE))
            copy($files_dir . Constants::NEIGHBOUR_TREE, $project_dir . '/' . Constants::NEIGHBOUR_TREE);
        // 2.4 UPGMA tree.jpg
        if(file_exists($files_dir . Constants::UPGMA_TREE))
            copy($files_dir . Constants::UPGMA_TREE, $project_dir . '/' . Constants::UPGMA_TREE);
        // 2.5 DistanceMatrix.txt
        if(file_exists($files_dir . "DistanceMatrix.txt"))
            copy($files_dir . "DistanceMatrix.txt", $project_dir . '/DistanceMatrix.txt');
        // 2.6 config.json
        file_put_contents($project_dir . '/' . Constants::CONFIG_JSON, json_encode($this->_config, JSON_PRETTY_PRINT));
        // 2.7 SpeciesRelation.json
        if(file_exists($files_dir . "SpeciesRelation.json"))
            copy($files_dir . "SpeciesRelation.json", $project_dir . '/SpeciesRelation.json');
        return true;
    }

    private function send_mail(){
        $user_info = (new User())->get_info($this->_user_id);
        $project_link = self::WEB_ADDRESS . '/projects/' . $this->_project_id;
        $subject = 'Project Execution Completed';
        $body    = <<< EOF
<p>Congratulations!</p>
<p>
    The project named '{$this->_config['project_name']}' is completed successfully.
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
        extract($this->_config);
        /**
         * @var string $type          file or accn_gin
         * @var string $sequence_type Minimal Absent Word Type (nucleotide|protein)
         */
        if($type == Constants::PROJECT_TYPE_FILE){
            return $this->move_uploaded_files();
        }else /* if($type == Constants::PROJECT_TYPE_ACCN_GIN) */{
            return $this->download_fasta($sequence_type);
        }
    }

    /**
     * @return bool
     */
    private function generateAW(){
        extract($this->_config);
        /**
         * variables extracted from $this->_config
         * (Only the relevant ones)
         *
         * @var string $aw_type       Absent Word Type (maw|raw)
         */
        if($aw_type == 'maw'){
            return $this->generate_maw();
        }else /* if($aw_type == 'raw')*/ {
            return $this->generate_raw();
        }
    }

    private function generate_phylogenic_trees(){
        exec(self::EXECS['match7'] . ' "'. $this->_dir->generated() .'/"', $output, $return);
        error_log(implode("\n", $output));
        return $return === 0 ? true : false;
    }

    private function generate_distance_matrix(){
        extract($this->_config);
        /**
         * variables extracted from $this->_config
         * (Only the relevant ones)
         *
         * @var string $aw_type       Absent Word Type (maw|raw)
         * @var string $sequence_type Minimal Absent Word Type (nucleotide|protein)
         * @var array  $kmer          K-Mer [min, max]
         * @var bool   $inversion     Use Inversion ?
         * @var string $dissimilarity_index Dissimilarity Index for MAW or RAW
         * @var array  $data
         */
        // set target directory
        $target = $this->_dir->generated();
        // Create SpeciesFull.txt
        $this->gen_species_full();
        // Run Distance Matrix Generator
        $aw_type = strtoupper($aw_type);
        exec(self::EXECS['dm'][$this->_platform] . " {$aw_type} {$dissimilarity_index} {$target} {$target}", $output, $return);
        error_log(implode("\n", $output)); unset($output);
        return $return === 0 ? true : false;
    }


    /**
     * @return bool
     */
    private function generate_maw(){
        extract($this->_config);
        /**
         * variables extracted from $this->_config
         * (Only the relevant ones)
         *
         * @var string $aw_type       Absent Word Type (maw|raw)
         * @var string $sequence_type Minimal Absent Word Type (nucleotide|protein)
         * @var array  $kmer          K-Mer [min, max]
         * @var bool   $inversion     Use Inversion ?
         * @var string $dissimilarity_index Dissimilarity Index for MAW or RAW
         * @var array  $data
         */
        $sequence_type = ($sequence_type == 'nucleotide') ? 'DNA' : 'PROT';
        // Generate {short_name}.maw.txt from the input fasta files
        foreach($this->get_files($this->_dir->original()) as $file){
            // Filename: {species_name}.maw.txt
            $output_file = $this->_dir->generated() . '/' . basename($file, '.fasta') . '.maw.txt';
            exec(self::EXECS['maw'][$this->_platform] . " -a {$sequence_type} -i '{$file}' -o '{$output_file}' -k {$kmer["min"]} -K {$kmer["max"]}" . ($inversion ? ' -r 1' : ''), $output, $return);
            error_log(implode("\n", $output));
            if($return !== 0){
                $this->delete_project("Generating maw failed for " . $file);
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
         extract($this->_config);
         /**
          * variables extracted from $this->_config
          * (Only the relevant ones)
          *
          * @var array  $kmer         K-Mer [max, min]
          * @var bool   $inversion    Use Inversion ?
         */
         // Initial tasks
         if($this->_ref_index == 0){
             $this->_files = $this->get_files($this->_dir->original());
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
                 $this->delete_project("Generating raw failed!");
                 return false;
             }
         };
         // 3. Get the species_name from the reference file
         $ref_file_name = basename($ref_file, '.fasta');
         // 4. Create a /tmp/Projects/{project_id}/Files/generated/{species_name} directory
         $ref_file_dir = $this->_dir->generated() . '/' . $ref_file_name;
         if(!file_exists($ref_file_dir)) mkdir($ref_file_dir);
         // 5. Generate {species_name}.raw.txt in the /tmp/Projects/{project_id}/Files/original directory
         foreach($modified_files as $file){
             exec(self::EXECS['raw'][$this->_platform] . " -min {$kmer["min"]} -max {$kmer["max"]}" . ($inversion ? ' -i' : '') . " -r '{$ref_file}' '{$file}'", $output);
             error_log(implode("\n", $output));
             // Move the *.raw.txt files to the /tmp/Projects/{project_id}/Files/generated/raw/{species_name} directory
             passthru("mv '{$this->_dir->original()}'/*.raw.txt '{$ref_file_dir}'");
         }
         // increment by one
         ++$this->_ref_index;
         return $this->generate_raw();
    }


    /**
     * @return bool
     */
    private function move_uploaded_files(){
        extract($this->_config);
        /**
         * variables extracted from $this->_config
         *
         * @var array  $data
         * @var string $file_id       md5 sum of file directory
         */

        // Get the temporary upload directory
        $upload_directory = null;
        foreach ($this->_uploaded_files as &$item){
            if($item['md5'] === $file_id){
                $upload_directory = $item['dir'];
            }
        }

        // Cancel project if no valid directory found: this is highly unlikely to be happened
        if($upload_directory === null){
            $this->delete_project("Upload directory cannot be found!");
            return false;
        }

        // Get file names
        $files = $this->get_files($upload_directory);
        if(count($files) <= 0){
            $this->delete_project("Upload directory is empty!");
            return false;
        }

        $f_names = $this->full_name_to_short_name($data);

        // cd to the original directory
        if(!$this->_dir->cd($this->_dir->original())) $this->_dir->create();
        // Move file to the pwd
        foreach ($files as $file){
            if(!$this->_dir->store($f_names[basename($file, '.fasta')] . '.fasta', $file, $this->_dir::STORE_MOVE)){
                $this->delete_project("Moving {$file} failed!");
                return false;
            }
        }
        // Delete the directory where the extracted files are previously stored, along with session
        exec("rm -R {$upload_directory}");
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
        $data        = $this->_config['data'];
        $database    = $sequence_type == 'protein' ? 'protein' : 'nuccore';
        $gin_count   = count($data);

        if(!$this->_dir->cd($this->_dir->original())) $this->_dir->create();
        for($i = 0; $i < $gin_count; ++$i){
            //copy("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db={$database}&id={$data[$i]['gin']}&rettype=fasta&retmode=text", $dir . '/' . $data[$i]['short_name'] . '.fasta');
            if(!$this->_dir->store($data[$i]['short_name'] . '.fasta', "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db={$database}&id={$data[$i]['gin']}&rettype=fasta&retmode=text", $this->_dir::STORE_DOWNLOAD)){
                $this->delete_project("{$data[$i]['gin']} can't be downloaded");
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
            $assoc[($this->_config['type'] == Constants::PROJECT_TYPE_ACCN_GIN ? $data[$i]['title'] : $data[$i]['id'])] = $data[$i]['short_name'];
        }
        return $assoc;
    }

    private function delete_project($message = null){
        if($message !== null) error_log($message);
        return (new Project())->delete($this->_project_id, $this->_user_id) === 0 ? true : false;
    }

    private function get_files($dir){
        if(!$this->_dir->cd($dir, true)){
            $this->delete_project("{$dir} doesn't exist!");
            return [];
        }
        return $this->_dir->getAll();
    }

    /**
     * gen_species_full method.
     *
     * Generates SpeciesFull.txt
     */
     private function gen_species_full(){
        $species = [];
        $data    = $this->_config['data'];
        foreach($data as $datum){
            array_push($species, $datum['short_name']);
        }
        array_push($species, '');    // Won't work without this!!! (because dm used gets() to read lines)
        // Export file
        file_put_contents($this->_dir->generated() . '/SpeciesFull.txt', implode("\n", $species));
    }
}