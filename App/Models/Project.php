<?php

namespace AWorDS\App\Models;

use \AWorDS\App\Constants;
use \AWorDS\Config;
//use Cocur\BackgroundProcess\BackgroundProcess;

/**
 * @property int|null project_id
 * @property string   project_dir
 * @property string   files_dir
 * @property string   original_dir
 * @property string   generated_dir
 * @property string   maw_dir
 * @property string   raw_dir
 * @property string   exec_location
 * @property array    files
 */
class Project extends Model{
    private $dm_exec     = 'dm';
    private $maw_exec    = 'maw';
    private $eagle_exec  = 'EAGLE';
    private $project_id;

    protected $config    = [];

    public $dissimilarity_index = [
        "MAW" => [
            "MAW_LWI_SDIFF" => "Length weighted index of symmetric difference of MAW sets",
            "MAW_LWI_INTERSECT" => "Length weighted index of intersection of MAW sets",
            "MAW_GCC_SDIFF" => "GC content of symmetric difference of MAW sets",
            "MAW_GCC_INTERSECT" => "GC content of intersection of MAW sets",
            "MAW_JD" => "Jaccard Distance of MAW sets",
            "MAW_TVD" => "Total Variation Distance of MAW sets"
        ],
        "RAW" => [
            "RAW_LWI" => "Length weighted index of RAW set",
            "RAW_GCC" => "GC content of RAW set"
        ]
    ];

    /**
     * Public Functions
     */

    /**
     * new_project method
     *
     * Creates a new project
     *
     * // FIXME: this should only create a new project and return the project_id
     * 
     * @param array $config
     * @return null|int returns the id number on success, null on failure
     */
    function add($config){
        $this->config = $config;
        // Check if the config file is in order
        if(!$this->is_a_config()) return null;
        // If so, create a new project
        extract($this->config);
        /**
         * variables extracted from $this->config
         *
         * @var string $project_name  Name of the project
         */
        $project_id = null;
        // Save project info in the DB, and get inserted id (which is the project id)
        if(@$stmt = $this->mysqli->prepare('INSERT INTO `projects`(`user_id`, `project_name`, `date_created`) VALUE(?, ?, NOW())')){
            $stmt->bind_param('is', $_SESSION['user_id'], $project_name);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE){
                // Set the inset_id as the project id
                $project_id = $stmt->insert_id;
                // Add to pending list
                (new PendingProjects())->add($project_id);
                // Set this project as the last project
                (new LastProjects())->set($project_id);
                // Create necessary directories
                $dir = new Directories($project_id);
                $dir->create();
                // Store config.json to the root directory
                $dir->store(Constants::CONFIG_JSON, json_encode($config, JSON_PRETTY_PRINT), $dir::STORE_STRING);
                // Set project id
                $this->project_id = $project_id;
                // Begin processing data
                $this->process();
                // Success
                return $project_id;
            }
        }
        return null;
    }

    /**
     * uploadFile method.
     *
     * Uploads a valid file: verify file upload
     *
     * @param array $zip Contains file related info
     * @return array File upload status, and an array of species names on success
     */
    function uploadFile($zip){
        // Create upload directory array
        if(!isset($_SESSION['upload_info'])){
            /** @var array $_SESSION[upload_info] 0 => [dir => path/to/fasta, md5 => md5(path/to/fasta)] */
            $_SESSION['upload_info'] = [];
        }

        // 1. Size limit
        if($zip['size'] > Config::MAX_UPLOAD_SIZE) return ['status' => Constants::FILE_SIZE_EXCEEDED];
        // 2. MIME: not always check-able: skip
        //if(!($zip['type'] == 'application/zip' || $zip['type'] == 'application/octet-stream')) return ['status' => Constants::FILE_INVALID_MIME];
        // 3. See if it can be moved
        $tmp_dir = '/tmp/' . (time() + mt_rand());
        mkdir($tmp_dir);
        $tmp_file = $tmp_dir . '/' . basename($zip['name']);
        if(!move_uploaded_file($zip['tmp_name'], $tmp_file)) return ['status' => Constants::FILE_INVALID_FILE];
        // 4. Is it a valid zip?
        $zip_archive = new \ZipArchive();
        if($zip_archive->open($tmp_file) === true){
            // Extract file to $tmp_dir
            $zip_archive->extractTo($tmp_dir);
            $zip_archive->close();
            // Delete the $tmp_file
            unlink($tmp_file);
            // 5. Is everything in order?
            $files = $this->dir_list($tmp_dir, true);
            // 5.1 Each file size limit check + extract FASTA from multi FASTA
            $data = [];
            foreach($files as $file){
                $data = array_merge($data, $this->extract_FASTA($file, $tmp_dir));
                foreach ($data as $datum){
                    $_file = $tmp_dir . '/' . $datum['id'] . '.fasta';
                    // If a single file size exceeds the MAX_FILE_SIZE, show error
                    if(filesize($_file) > Config::MAX_FILE_SIZE) return ['status' => Constants::FILE_SIZE_EXCEEDED];
                }
            }
            // Everything's in order
            // Set file session containing the extracted directory
            $id = md5($tmp_dir);
            array_push($_SESSION['upload_info'], ['dir' => $tmp_dir, 'md5' => $id]);
            // Return success
            return ['status' => Constants::FILE_UPLOAD_SUCCESS, 'data' => $data, 'id' => $id];
        }
        // In any other case, return invalid file
        return ['status' => Constants::FILE_INVALID_FILE];
    }

    /**
     * all_projects method.
     *
     * Returns all the projects associated with the respective user
     *
     * @return array containing 'id', 'name', 'date_created' and 'editable' or just empty array on failure
     */
    function getAll(){
        $projects = [];
        // Last project id
        $last_project_id = (new LastProjects())->get();
        // Get projects info in descending order
        if(@$stmt = $this->mysqli->prepare('SELECT `project_id`, `project_name`, `date_created` FROM `projects` WHERE `user_id` = ? ORDER BY `date_created` DESC')){
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            $pending_projects = (new PendingProjects())->getAll(true);
            if($pending_projects === false) $pending_projects = [];
            for($i = 0; $i < $stmt->num_rows; $i++){
                $project = ['id' => null, 'name' => null, 'date_created' => null, 'editable' => false, 'pending' => false];

                $stmt->bind_result($project['id'], $project['name'], $project['date_created']);
                $stmt->fetch();
                // If a project id matches with the last project id and is not pending, the project is editable
                if($project['id'] == $last_project_id) $project['editable'] = true;
                if(in_array($project['id'], $pending_projects)){
                    $project['pending'] = true;
                    $project['editable'] = false;
                }
                // Push the project to the array
                array_push($projects, $project);
            }
        }
        return $projects;
    }

    /**
     * export method
     *
     * Exports SpeciesRelation, DistanceMatrix, UPGMA Tree, Neighbour Tree and a zipped file
     * based on the user request
     *
     * @param int $project_id The project ID of which content is needed to be shown
     * @param int $type       Which type of file is requested
     * @return null|array     array containing mime, name, path on success, null on failure
     */
    function export($project_id, $type){
        $file = null;
        // Verify project id
        if(!$this->verify($project_id)) return $file;
        // Set project info
        switch($type){
            case Constants::EXPORT_SPECIES_RELATION:
                $file['mime'] = 'text/plain';
                $file['name'] = Constants::SPECIES_RELATION;
                $file['path'] = Config::PROJECT_DIRECTORY . '/' . $project_id . '/' . Constants::SPECIES_RELATION;
                break;
            case Constants::EXPORT_DISTANT_MATRIX:
                $file['mime'] = 'text/plain';
                $file['name'] = 'DistanceMatrix.txt';
                $file['path'] = Config::PROJECT_DIRECTORY . '/' . $project_id . '/' . Constants::DISTANT_MATRIX;
                break;
            case Constants::EXPORT_NEIGHBOUR_TREE:
                $file['mime'] = 'image/jpeg';
                $file['name'] = Constants::NEIGHBOUR_TREE;
                $file['path'] = Config::PROJECT_DIRECTORY . '/' . $project_id . '/' . Constants::NEIGHBOUR_TREE;
                break;
            case Constants::EXPORT_UPGMA_TREE:
                $file['mime'] = 'image/jpeg';
                $file['name'] = Constants::UPGMA_TREE;
                $file['path'] = Config::PROJECT_DIRECTORY . '/' . $project_id . '/' . Constants::UPGMA_TREE;
                break;
            case Constants::EXPORT_ALL:
                $file['mime'] = 'application/zip';
                $file['name'] = 'project_' . $project_id . '.zip'; // e.g. project_29
                $file['path'] = '/tmp/'. $file['name'];
                // Create zip
                $zip = new \ZipArchive();
                if ($zip->open($file['path'], \ZipArchive::CREATE)!==TRUE) {
                    return null;
                }
                // Add files to zip
                $project_dir = Config::PROJECT_DIRECTORY . '/' . $project_id;
                $zip->addFile($project_dir . '/' . Constants::SPECIES_RELATION, '/' . Constants::SPECIES_RELATION);
                $zip->addFile($project_dir . '/' . Constants::DISTANT_MATRIX, '/DistanceMatrix.txt');
                $zip->addFile($project_dir . '/' . Constants::NEIGHBOUR_TREE, '/' . Constants::NEIGHBOUR_TREE);
                $zip->addFile($project_dir . '/' . Constants::UPGMA_TREE, '/' . Constants::UPGMA_TREE);
                $zip->addFile($project_dir . '/config.json', '/config.json');
                $zip->close();
        }
        // set $file to null if the file isn't found
        if(!file_exists($file['path'])) $file = null;
        return $file;
    }

    /**
     * delete method
     *
     * Permanently deletes a project
     *
     * @param int $project_id The project id which will be deleted
     * @param int|null $user_id
     * @return int Status
     */
    function delete($project_id, $user_id = null){
        if($user_id === null) $user_id = $_SESSION['user_id'];
        // If the project to be deleted is the last project of the user,
        // delete it from that table too.
        $lastProject = new LastProjects($user_id);
        if($lastProject->isA($project_id)) $lastProject->remove();
        // Delete as pending project if it is in the list
        $pendingProject = new PendingProjects($project_id, $user_id);
        if($pendingProject->isA($project_id)) $pendingProject->remove();
        // Delete project from the database
        if(@$stmt = $this->mysqli->prepare('DELETE FROM `projects` WHERE `user_id` = ? AND `project_id` = ?')){
            $stmt->bind_param('ii', $user_id, $project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE){
                // Delete the project files
                exec('rm -rf "' . Config::PROJECT_DIRECTORY . '/' . $project_id . '"');
                // Delete was a success
                return Constants::PROJECT_DELETE_SUCCESS;
            }else return Constants::PROJECT_DOES_NOT_EXIST;
        }
        return Constants::PROJECT_DELETE_FAILED;
    }

    /**
     * verify method
     *
     * Verifies a project against the current user
     *
     * @param int $project_id
     * @return bool
     */
    function verify($project_id){
        if(@$stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM projects WHERE user_id = ? AND project_id = ?')){
            $stmt->bind_param('ii', $_SESSION['user_id'], $project_id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            if($count == Constants::COUNT_ONE) return true;
        }
        return false;
    }

    /**
     * FIXME
     */
    function process(){
        //require_once __DIR__ . '/../../Libraries/background-process/BackgroundProcess.php';

//        $data = [
//            'project_id' => $this->project_id,
//            'user_id'    => $_SESSION['user_id'],
//            'uploaded_files' => isset($_SESSION['upload_info']) ? $_SESSION['upload_info'] : null
//        ];

        (new Process($this->project_id, $_SESSION['user_id'], ($this->config['type'] == Constants::PROJECT_TYPE_FILE ? $_SESSION['upload_info'] : null)))->init();


        //$cmd = 'php ' . __DIR__ . '/../../exec/process.php \'' . json_encode($data) . '\' >/dev/null 2>&1 &';
        //$cmd = 'php ' . __DIR__ . '/../../exec/process.php >/dev/null 2>&1 &';
        //system('php ' . __DIR__ . '/../../exec/process.php \'' . json_encode($data) . '\'');
        //system('bash -c "exec nohup setsid ' . $cmd . '"');
        //print_r(implode('\n', $output));
        //        $process = new BackgroundProcess('php ' . __DIR__ . '/../../exec/process.php \'' . json_encode($data) . '\'');
//        $process->run();
//        error_log("PID: " . $process->getPid());
//        error_log("Is running: " . $process->isRunning());
    }

    /**
     * May be in a separate class
     */


    /**
     * @return int
     */
    function unread_count(){
        if(@$stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM projects WHERE user_id = ? AND seen = 0')){
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            return $count;
        }
        return 0;
    }

    function unread_projects_info(){
        $projects = [];
        if(@$stmt = $this->mysqli->prepare('SELECT project_id, project_name, date_created FROM projects WHERE user_id = ? AND seen = 0 ORDER BY project_id DESC')){
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            for($i = 0; $i < $stmt->num_rows; ++$i){
                $project = [];
                $stmt->bind_result($project['id'], $project['name'], $project['date_created']);
                $stmt->fetch();
                array_push($projects, $project);
            }
        }
        return $projects;
    }

    function set_seen($project_id){
        if(@$stmt = $this->mysqli->prepare('UPDATE `projects` SET `seen` = 1 WHERE project_id = ? AND user_id = ?')){
            $stmt->bind_param('ii', $project_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE){
                return true;
            }
        }
        return false;
    }

    /**
     * Private functions
     */

    /**
     * dir_list method
     *
     * @param string $directory Any directory
     * @param bool   $full      Whether to include full directory (takes more time)
     * @return array List of directories or empty array if not a directory
     */
    private function dir_list($directory, $full = true){
        // Remove `.` and `..` from the list
        $files = is_dir($directory) ? array_diff(scandir($directory), array('..', '.')) : [];
        // Generate full path filename if $full is true
        if($full){
            foreach($files as &$file) $file = $directory . '/' . $file;
        }
        return $files;
    }

    /**
     * is_a_config method
     *
     * Check whether the provided config is valid or not
     * Note: This method cannot check the other fields when not uploading a file
     *
     * @return bool
     */
    private function is_a_config(){
        extract($this->config);
        /**
         * variables extracted from $this->config
         *
         * @var string $project_name  Name of the project
         * @var string $aw_type       Absent Word Type (maw|raw)
         * @var string $sequence_type Minimal Absent Word Type (nucleotide|protein)
         * @var array  $kmer          K-Mer [max, min]
         * @var bool   $inversion     Use Inversion ?
         * @var string $dissimilarity_index Dissimilarity Index for MAW or RAW
         * @var array  $data          Containing all the InputAnalyzer.results data
         * @var string $type          FASTA file getting method (file|accn_gin)
         * @var string $file_id       md5 sum of uploaded file directory
         */

        $d_i_maw = array_keys($this->dissimilarity_index['MAW']); //['MAW_LWI_SDIFF', 'MAW_LWI_INTERSECT', 'MAW_GCC_SDIFF', 'MAW_GCC_INTERSECT', 'MAW_JD', 'MAW_TVD'];
        $d_i_raw = array_keys($this->dissimilarity_index['RAW']); //['RAW_LWI', 'RAW_GCC'];

        if(isset($project_name) AND $project_name != null
            AND isset($aw_type) AND in_array($aw_type, ['maw', 'raw'])
            AND isset($kmer, $kmer['min'], $kmer['max'])
            AND isset($inversion)
            AND ($aw_type == 'maw' OR $aw_type == 'raw')
            AND isset($sequence_type) AND in_array($sequence_type, ['nucleotide', 'protein'])
            AND isset($dissimilarity_index)
            AND (($aw_type == 'maw'   AND in_array($dissimilarity_index, $d_i_maw))
                OR ($aw_type == 'raw' AND in_array($dissimilarity_index, $d_i_raw)))
            AND isset($type) AND in_array($type, ['file', 'accn_gin'])
            AND (($type == 'file' AND isset($_SESSION['upload_info'], $file_id) AND $this->has_this_file_directory($file_id)) OR ($type == 'accn_gin'))
            AND isset($data)
        )
            return true;

        return false;
    }

    /**
     * Search for uploaded file directory from a md5 sum (known as file_id)
     * @param string $md5_sum
     * @return bool
     */
    private function has_this_file_directory($md5_sum){
        foreach ($_SESSION['upload_info'] as $item){
            if($item['md5'] === $md5_sum) return true;
        }
        return false;
    }

    /**
     * Extracts single FASTA from multiple FASTA
     *
     * Note: The output file is located at $target/$id.fasta
     *
     * @param string $filename The FASTA file containing multiple items
     * @param string $target   Target directory
     * @return array associative array [header, id]
     */
    private function extract_FASTA($filename, $target){
        $source_fp = fopen($filename, 'r');
        $data = [];
        $count = 0;
        while(!feof($source_fp)){
            $line = fgets($source_fp);
            if(substr($line, 0, 1) === '>'){ // header is found
                $header = trim(substr($line, 1, strlen($line)-1));
                do{
                    $id = time() + ($count++);
                    $file_name = $target . '/' . $id . ".fasta";
                }while(file_exists($file_name));

                $target_fp = fopen($file_name, 'w');
                $info = ["header" => $header, "id" => $id];
                array_push($data, $info);
            }
            if(isset($target_fp)) fwrite($target_fp, $line);
        }
        unlink($filename);
        return $data;
    }
}
