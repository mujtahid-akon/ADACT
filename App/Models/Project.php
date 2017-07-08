<?php

namespace AWorDS\App\Models;

use \AWorDS\App\Constants;
use \AWorDS\Config;

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
    private $fasta_count = 0;
    private $ref_index   = 0;
    protected $config    = [];

    /**
     * Public Functions
     */

    /**
     * new_project method
     *
     * Creates a new project
     * 
     * @param array $config
     * @return null|int returns the id number on success, null on failure
     */
    function new_project($config){
        $this->config = $config;
        // Check if the config file is in order
        if(!$this->is_a_config()) return null;
        // If so, execute
        return $this->execute(true);
    }
    
    /**
     * file_upload method.
     *
     * Uploads a valid file: verify file upload
     *
     * @param array $zip containing file related info
     * @return array file upload status, and an array of Species names on success
     */
    function file_upload($zip){
        // 1. Size limit
        if($zip['size'] > Config::MAX_UPLOAD_SIZE) return ['status' => Constants::FILE_SIZE_EXCEEDED];
        // 2. MIME: not always check-able: skip
        //if(!($zip['type'] == 'application/zip' || $zip['type'] == 'application/octet-stream')) return ['status' => Constants::FILE_INVALID_MIME];
        // 3. See if it can be moved
        $tmp_dir = '/tmp/' . time();
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
            // 5.1 Does it contain SpeciesOrder.txt and SpeciesFull.txt? : skip (will be generated instead)
            // count(preg_grep('/SpeciesOrder\.txt$/', $files)) != Constants::COUNT_ONE AND 
            //if(count(preg_grep('/SpeciesFull\.txt$/', $files)) != Constants::COUNT_ONE) return ['status' => Constants::FILE_INVALID_FILE];
            // 5.2 Each file size limit check
            foreach($files as &$file){
                // If a single file size exceeds the MAX_FILE_SIZE, show error
                if(filesize($file) > Config::MAX_FILE_SIZE) return ['status' => Constants::FILE_SIZE_EXCEEDED];
                $file = $this->get_basename($file);
            }
            // Reindex files (now only file names)
            sort($files);
            // Everything's in order
            // Set file session containing the extracted directory
            $_SESSION['file_dir'] = $tmp_dir;
            // Return success
            return ['status' => Constants::FILE_UPLOAD_SUCCESS, 'names' => $files];
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
    function all_projects(){
        $projects = [];
        // Last project id
        $last_project_id = $this->last_project_id();
        // Get projects info in descending order
        if($stmt = $this->mysqli->prepare('SELECT `project_id`, `project_name`, `date_created` FROM `projects` WHERE `user_id` = ? ORDER BY `date_created` DESC')){
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            for($i = 0; $i < $stmt->num_rows; $i++){
                $project = ['id', 'name', 'date_created', 'editable' => false];
                $stmt->bind_result($project['id'], $project['name'], $project['date_created']);
                $stmt->fetch();
                // If a project id matches with the last project id, the project is editable
                if($project['id'] == $last_project_id) $project['editable'] = true;
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
     * @return null
     */
    function export($project_id, $type){
        $file = null;
        // Verify project id
        if(!$this->verify_project($project_id)) return $file;
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
                $file['name'] = 'project_' . $project_id; // e.g. project_29
                $file['path'] = 'path/to/dir'; // FIXME: path isn't defined
        }
        // set $file to null if the file isn't found
        if(!file_exists($file['path'])) $file = null;
        return $file;
    }

    /**
     * delete_project method
     *
     * Permanently deletes a project
     *
     * @param int $project_id The project id which will be deleted
     * @return int status
     */
    function delete_project($project_id){
        // If the project to be deleted is the last project of the user,
        // delete it from that table too.
        if($this->last_project_id() == $project_id) $this->delete_as_last();
        // Delete project from the database
        if($stmt = $this->mysqli->prepare('DELETE FROM `projects` WHERE `user_id` = ? AND `project_id` = ?')){
            $stmt->bind_param('ii', $_SESSION['user_id'], $project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE){
                // Delete the project files
                passthru('rm -rf "' . Config::PROJECT_DIRECTORY . '/' . $project_id . '"');
                // Delete was a success
                return Constants::PROJECT_DELETE_SUCCESS;
            }else return Constants::PROJECT_DOES_NOT_EXIST;
        }
        return Constants::PROJECT_DELETE_FAILED;
    }

    /**
     * last_project_id method.
     *
     * Last project ID means: the last EDITABLE project ID done by a user
     * If this project is deleted, the user won't have any editable project
     *
     * @return int|null last project id on success, null on failure
     */
    function last_project_id(){
        if($stmt = $this->mysqli->prepare('SELECT `project_id` FROM `last_projects` WHERE `user_id` = ?')){
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == Constants::COUNT_ONE){
                $stmt->bind_result($project_id);
                $stmt->fetch();
                return $project_id;
            }
        }
        return null;
    }


    /**
     * verify project method
     *
     * Verifies a project against the current user
     *
     * @param int $project_id
     * @return bool
     */
    function verify_project($project_id){
        if($stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM projects WHERE user_id = ? AND project_id = ?')){
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
     * last_project_info method
     *
     * Get last project-related info for the last user
     *
     * @return array|null an array containing 'id' and 'seen' on success, null on failure
     */
    function last_project_info(){
        if($stmt = $this->mysqli->prepare('SELECT `project_id`, `seen` FROM `last_projects` WHERE `user_id` = ?')){
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == Constants::COUNT_ONE){
                $stmt->bind_result($project_id, $seen_status);
                $stmt->fetch();
                return ['id' => $project_id, 'seen' => $seen_status];
            }
        }
        return null;
    }

    /**
     * set_last_project_seen method
     *
     * @return bool
     */
    function set_last_project_seen(){
        if($stmt = $this->mysqli->prepare('UPDATE `last_projects` SET `seen` = 1 WHERE `user_id` = ?')){
            $stmt->bind_param('i', $_SESSION['user_id']);
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
     * execute method.
     *
     * Executes user query to generate the requested result.
     * NOTE: This function is called only in the new_project() and edit_project()
     *
     * FIXME: edit project isn't implemented yet
     *
     * @param bool $new true = new project, false = edit project
     * @return int|null inserted project id on success or null on failure
     */
    private function execute($new){
        extract($this->config);
        /**
         * variables extracted from $this->config
         *
         * @var string $project_name Name of the project
         * @var string $aw_type      Absent Word Type (maw|raw)
         * @var string $maw_type     Minimal Absent Word Type (dna|protein)
         * @var int    $kmer_min     K-Mer minimum
         * @var int    $kmer_max     K-Mer maximum
         * @var bool   $inversion    Use Inversion?
         * @var string $dissimilarity_index Dissimilarity Index for MAW or RAW
         * @var array  $names        Full Species names
         * @var array  $short_names  Short Species names
         * @var string $type         FASTA file getting method (file|gin|accn)
         * @var array  $accn_numbers Accession numbers
         * @var array  $gi_numbers   GI numbers
         */
        $this->project_id = null;
        // 1. Save project info in the DB, and get inserted id (which is the project id)
        if($stmt = $this->mysqli->prepare('INSERT INTO `projects`(`user_id`, `project_name`, `date_created`) VALUE(?, ?, NOW())')){
            $stmt->bind_param('is', $_SESSION['user_id'], $project_name);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE){
                // Set the inset_id as the project id
                $this->project_id = $stmt->insert_id;
            }
        }
        // If the above query is successful
        if($this->project_id != null){
            // 2. Set this project as the last project by the user if it's a new project
            if($new) $this->set_as_last($this->project_id);
            // 3. Create a new temporary project using the project id in the /tmp/Projects dir
            // 3.1 Check the existence of the temporary directory as it may not exist
            if(!file_exists('/tmp/Projects/')) mkdir('/tmp/Projects/');
            // 3.2 Delete previous project folder if existed by any chance (although it's nearly 0%)
            //     and create a new directory
            $this->project_dir = '/tmp/Projects/' . $this->project_id;
            if(!file_exists($this->project_dir)) passthru('rm -rf ' . $this->project_dir);
            mkdir($this->project_dir);
            // 4. Move the extracted files (called at file_upload()) to the /tmp/Projects/{project_id}/Files
            // 4.1 Create 'Files' directory
            $this->files_dir = $this->project_dir . '/Files';
            if(!file_exists($this->files_dir)) mkdir($this->files_dir);
            // 4.2 Create 'original' directory inside 'Files'
            $this->original_dir = $this->files_dir . '/original';
            if(!file_exists($this->original_dir)) mkdir($this->original_dir);
            // 4.3 Move extracted files to the Files/original or download the files
            if($type == Constants::PROJECT_TYPE_FILE){
                // 4.3.1 Move extracted files to the Files/original
                $files = $this->dir_list($_SESSION['file_dir']);
                $f_names = $this->to_assoc($names, $short_names);
                foreach ($files as $file){
                    passthru('mv "' . $file . '" "' . $this->original_dir . '/' . $f_names[$this->get_basename($file)] . '.fasta"');
                }
                // 4.3.2 Delete the directory where the extracted files are previously stored, along with session
                passthru("rmdir {$_SESSION['file_dir']}");
                unset($_SESSION['file_dir']);
            }else{
                // 4.3.1 Download the FASTA files
                $this->download_fasta($this->original_dir);
            }

            // 5. Run scripts & Place files in the PROJECT_DIRECTORY/{project_id}/Files/
            //    from /tmp/Projects/{project_id}/Files
            $this->generate() AND $this->structure_files();
        }
        return $this->project_id;
    }

    /**
     * set_as_last method.
     *
     * Set the project id as the last project for the current user
     * NOTE: This method is only called by new_project() functions child function execute()
     *       if execute() is called by edit_project(), it'll be ignored
     *
     * @param int $project_id
     */
    private function set_as_last($project_id){
        // Delete the last project id provided they are not the same
        $last_project_id = $this->last_project_id();
        if($last_project_id != $project_id){
            $this->delete_as_last();
            // Also delete the PROJECT_DIRECTORY/{last_project_id}/Files folder as
            // it is only intended for the last project
            passthru('rm -rf "' . Config::PROJECT_DIRECTORY . '/' . $last_project_id . '/Files"');
        }else return;
        // Set the current project id as the last project id
        if($stmt = $this->mysqli->prepare('INSERT INTO `last_projects` VALUE(?, ?, 0)')){
            $stmt->bind_param('ii', $_SESSION['user_id'], $project_id);
            $stmt->execute();
            $stmt->store_result();
        }
    }

    /**
     * delete_as_last method.
     *
     * Delete the project id of the current user from the last_projects table
     */
    private function delete_as_last(){
        if($stmt = $this->mysqli->prepare('DELETE FROM `last_projects` WHERE `user_id` = ?')){
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
        }
    }

    /**
     * structure_files method
     *
     * Move files to the Projects/{project_id}
     * NOTE: this method is called at execute() right after calling the generate()
     *
     * @return true does not do anything, but still important
     */
    private function structure_files(){
        $project_dir = Config::PROJECT_DIRECTORY . '/' . $this->project_id;
        error_log($project_dir);
        // 1. Move /tmp/Projects/{project_id}/ to /Projects/{project_id}/
        passthru("mv \"{$this->project_dir}\" \"{$project_dir}/\"");
        // Notice: the trailing slash
        $files_dir = $project_dir . '/Files/generated/' . $this->config['aw_type'] . '/';
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
        // 2.5 config.json
        file_put_contents($project_dir . '/' . Constants::CONFIG_JSON, json_encode($this->config, JSON_PRETTY_PRINT));
        // 2.6 SpeciesOrder.txt: @deprecated
//        if(file_exists($files_dir . Constants::SPECIES_ORDER))
//            copy($files_dir . Constants::SPECIES_ORDER, $project_dir . '/' . Constants::SPECIES_ORDER);
        return true;
    }

    /**
     * generate method.
     *
     * Generates {species_name}.[m|r]aw.txt, then generates the required files from them
     *
     * @return true do nothing, but important
     */
    private function generate(){
        $s = time();    // Running time begin
        extract($this->config);
        /**
         * variables extracted from $this->config
         *
         * @var string $project_name Name of the project
         * @var string $aw_type      Absent Word Type (maw|raw)
         * @var string $maw_type     Minimal Absent Word Type (dna|protein)
         * @var int    $kmer_min     K-Mer minimum
         * @var int    $kmer_max     K-Mer maximum
         * @var bool   $inversion    Use Inversion?
         * @var string $dissimilarity_index Dissimilarity Index for MAW or RAW
         * @var array  $names        Full Species names
         * @var array  $short_names  Short Species names
         * @var string $type         FASTA file getting method (file|gin|accn)
         * @var array  $accn_numbers Accession numbers
         * @var array  $gi_numbers   GI numbers
         */

        $this->exec_location = __DIR__ . '/../../exec';

        // 1. Lists all the .fasta & .fna files
        $this->files = $this->dir_list($this->original_dir);
        
        // 2. Create MAW and RAW directories if not exist
        $this->generated_dir = $this->files_dir . '/generated';
        $this->maw_dir       = $this->generated_dir . '/maw';
        $this->raw_dir       = $this->generated_dir . '/raw';
        
        if(!file_exists($this->generated_dir)) mkdir($this->generated_dir);
        if(!file_exists($this->maw_dir)) mkdir($this->maw_dir);
        if(!file_exists($this->raw_dir)) mkdir($this->raw_dir);
        
        // 3. Generate *.[m|r]aw.txt files
        if($aw_type == 'maw'){
            $maw_type = ($maw_type == 'dna') ? 'DNA' : 'PROT';
            // Generate {species_name}.maw.txt from the input fasta files
            foreach($this->files as $file){
                // Filename: {species_name}.maw.txt
                $output_file = $this->maw_dir . '/' . $this->get_basename($file) . '.maw.txt';
                exec("{$this->exec_location}/maw -a {$maw_type} -i '{$file}' -o '{$output_file}' -k {$kmer_min} -K {$kmer_max}" . ($inversion ? ' -r 1' : ''), $output);
                error_log(implode("\n", $output));
            }
        }elseif($aw_type == 'raw'){
            // Sort $files to reset index numbers
            sort($this->files);
            // Generate {species_name}.raw.txt from the input fasta files
            $this->fasta_count = count($this->files);
            $this->ref_index   = 0;
            $this->generate_raw();
        }

        // set target directory by Absent Word Type (aw_type)
        $target = ($aw_type == 'maw') ? $this->maw_dir : $this->raw_dir;
        // Create SpeciesFull.txt
        $this->gen_species_full($this->files, $target);
        // Run Distance Matrix Generator
        $aw_type = strtoupper($aw_type);
        exec("{$this->exec_location}/dm {$aw_type} {$dissimilarity_index} {$target} {$target}", $output);
        error_log(implode("\n", $output));
        // Generate trees
        exec("cd {$this->exec_location} && java Match7 \"{$target}/\"", $output);
        error_log(implode("\n", $output));
        $e = time();    // Running time end
        error_log("Time taken: " . ($e - $s));
        return true;
    }

    /**
     * generate_raw method
     *
     * generates {species_name}.raw.txt by compare it to all the other ones
     *
     * @return null
     */
    private function generate_raw(){
        extract($this->config);
        /**
         * variables extracted from $this->config
         *
         * @var string $project_name Name of the project
         * @var string $aw_type      Absent Word Type (maw|raw)
         * @var string $maw_type     Minimal Absent Word Type (dna|protein)
         * @var int    $kmer_min     K-Mer minimum
         * @var int    $kmer_max     K-Mer maximum
         * @var bool   $inversion    Use Inversion?
         * @var string $dissimilarity_index Dissimilarity Index for MAW or RAW
         * @var array  $names        Full Species names
         * @var array  $short_names  Short Species names
         * @var string $type         FASTA file getting method (file|gin|accn)
         * @var array  $accn_numbers Accession numbers
         * @var array  $gi_numbers   GI numbers
         */

        // 1. Copy $this->files to $modified_files to prevent any data loss
        $modified_files = $this->files;
        // 2. Set a reference file ($ref_file)
        if($this->ref_index < $this->fasta_count){
            // Set reference file according to the
            $ref_file = $modified_files[$this->ref_index];
            // Delete the reference file name from the $modified_files
            unset($modified_files[$this->ref_index]);
        }else return null;
        // 3. Get the species_name from the reference file
        $ref_file_name = $this->get_basename($ref_file);
        // 4. Create a /tmp/Projects/{project_id}/Files/generated/raw/{species_name} directory
        $ref_file_dir = $this->raw_dir . '/' . $ref_file_name;
        if(!file_exists($ref_file_dir)) mkdir($ref_file_dir);
        // 5. Generate {species_name}.raw.txt in the /tmp/Projects/{project_id}/Files/original directory
        foreach($modified_files as $file){
            exec("{$this->exec_location}/EAGLE -min {$kmer_min} -max {$kmer_max}" . ($inversion ? ' -i' : '') . " -r {$ref_file} {$file}", $output);
            error_log(implode("\n", $output));
            // Move the *.raw.txt files to the /tmp/Projects/{project_id}/Files/generated/raw/{species_name} directory
            passthru("mv '{$this->original_dir}'/*.raw.txt '{$ref_file_dir}'");
        }
        // increment by one
        ++$this->ref_index;
        return $this->generate_raw();
    }

    /**
     * gen_species_full method.
     *
     * Generates SpeciesFull.txt from the provided files
     * NOTE: this function is currently called by generate() only
     *
     * @param array  $files      a list of files (full names don't matter)
     * @param string $target_dir where to save the SpeciousFull.txt file
     */
    private function gen_species_full($files, $target_dir){
        $species = [];
        // Get all the file names without extension (ie. species name)
        foreach($files as $file){
            array_push($species, $this->get_basename($file));
        }
        array_push($species, '');    // Won't work without this!!!
        // Export file
        file_put_contents($target_dir . '/SpeciesFull.txt', implode("\n", $species));
    }

    /**
     * get_basename method
     *
     * @param string $file full or relative file name
     * @return mixed returns the filename without extension
     */
    private function get_basename($file){
        return preg_replace('/(\.\w+)*$/', '', basename($file));
    }
    
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
         * @var string $project_name Name of the project
         * @var string $aw_type      Absent Word Type (maw|raw)
         * @var string $maw_type     Minimal Absent Word Type (dna|protein)
         * @var int    $kmer_min     K-Mer minimum
         * @var int    $kmer_max     K-Mer maximum
         * @var bool   $inversion    Use Inversion?
         * @var string $dissimilarity_index Dissimilarity Index for MAW or RAW
         * @var array  $names        Full Species names
         * @var array  $short_names  Short Species names
         * @var string $type         FASTA file getting method (file|gin|accn)
         * @var array  $accn_numbers Accession numbers
         * @var array  $gi_numbers   GI numbers
         */

        $d_i_maw = ['MAW_LWI_SDIFF', 'MAW_LWI_INTERSECT', 'MAW_GCC_SDIFF', 'MAW_GCC_INTERSECT', 'MAW_JD', 'MAW_TVD'];
        $d_i_raw = ['RAW_LWI', 'RAW_GCC'];

        if(isset($project_name) AND $project_name != null
            AND isset($aw_type) AND in_array($aw_type, ['maw', 'raw'])
            AND isset($kmer_min) AND preg_match('/\d+/', $kmer_min)
            AND isset($kmer_max) AND preg_match('/\d+/', $kmer_max)
            AND isset($inversion)
            AND ((($aw_type == 'maw') AND in_array($maw_type, ['dna', 'protein'])) OR $aw_type == 'raw')
            AND isset($dissimilarity_index)
            AND (($aw_type == 'maw' AND in_array($dissimilarity_index, $d_i_maw))
                OR ($aw_type == 'raw' AND in_array($dissimilarity_index, $d_i_raw)))
            AND isset($names, $short_names)
            AND isset($type) AND in_array($type, ['file', 'gin', 'accn'])
            AND (($type == 'file' AND isset($_SESSION['file_dir']))
                OR ($type == 'gin' AND isset($gi_numbers))
                OR ($type == 'accn' AND isset($gi_numbers, $accn_numbers))))
            return true;

        return false;
    }

    /**
     * download_fasta
     *
     * Download fasta from NCBI DB
     *
     * @param string $target
     */
    private function download_fasta($target){
        $short_names = $this->config['short_names'];
        $gi_numbers  = $this->config['gi_numbers'];
        $gin_count   = count($gi_numbers);
        for($i = 0; $i < $gin_count; ++$i){
            copy("https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=protein&id={$gi_numbers[$i]}&rettype=fasta&retmode=text", $target . '/' . $short_names[$i] . '.fasta');
        }
    }

    private function to_assoc($keys, $values){
        $assoc = [];
        $c_keys = count($keys);
        for($i = 0; $i<$c_keys; ++$i){
            $assoc[$keys[$i]] = $values[$i];
        }
        return $assoc;
    }
}
