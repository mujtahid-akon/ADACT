<?php

namespace AWorDS\App\Models;

use \AWorDS\App\Constants;
use \AWorDS\Config;

/**
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
    /**
     * Project Types
     */
    const PENDING_PROJECT = 1;
    const NEW_PROJECT     = 2;
    const LAST_PROJECT    = self::NEW_PROJECT;
    const REGULAR_PROJECT = 3;

    private $_project_id;
    /**
     * @var ProjectConfig
     */
    protected $config;

    function __construct($project_id = null){
        parent::__construct();
        $this->_project_id = $project_id;
    }

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
        $this->config = new ProjectConfig();
        $this->config->load_config($config);
        // Check if the config file is in order
        if(!$this->config->verify()) return null;
        // If so, create a new project
        $project_id = null;
        // Save project info in the DB, and get inserted id (which is the project id)
        if(@$stmt = $this->mysqli->prepare('INSERT INTO `projects`(`user_id`, `project_name`, `date_created`) VALUE(?, ?, NOW())')){
            $stmt->bind_param('is', $_SESSION['user_id'], $this->config->project_name);
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
                $dir = new FileManager($project_id);
                $dir->create();
                // Store config.json to the root directory
                $dir->store(FileManager::CONFIG_JSON, json_encode($config, JSON_PRETTY_PRINT), $dir::STORE_STRING);
                // Success
                return $project_id;
            }
        }
        return null;
    }

    /**
     * uploadFile method.
     *
     * FIXME: This should a controller
     *
     * Uploads a valid file: verify file upload
     *
     * @param array $zip_file Contains file related info
     * @return array File upload status, and an array of species names on success
     */
    function uploadFile($zip_file){
        $status = (new FileUploader())->upload($zip_file);
        if(is_array($status)){ // File uploaded successfully
            $status['status'] = FileUploader::FILE_UPLOAD_SUCCESS;
            return $status;
        }else{
            return ['status' => $status];
        }
    }

    /**
     * getAll method.
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
        if(@$stmt = $this->mysqli->prepare('SELECT project_id, project_name, CONVERT_TZ(date_created,\'SYSTEM\',\'UTC\') FROM `projects` WHERE `user_id` = ? ORDER BY `date_created` DESC')){
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
     * getAll method.
     *
     * Returns all the projects associated with the respective user
     *
     * @return array containing 'id', 'name', 'date_created' and 'editable' or just empty array on failure
     */
    function getAllPending(){
        $projects = [];
        // Get projects info in descending order
        if(@$stmt = $this->mysqli->prepare('SELECT a.project_id, a.project_name, CONVERT_TZ(a.date_created,\'SYSTEM\',\'UTC\') FROM projects AS a INNER JOIN pending_projects AS b ON a.project_id = b.project_id WHERE a.user_id = ?;')){
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            for($i = 0; $i < $stmt->num_rows; $i++){
                $project = ['id' => null, 'name' => null, 'date_created' => null];
                $stmt->bind_result($project['id'], $project['name'], $project['date_created']);
                $stmt->fetch();
                // Push the project to the array
                array_push($projects, $project);
            }
        }
        return $projects;
    }

    /**
     * get method.
     *
     * Returns all the projects associated with the respective user
     *
     * @param int $project_id
     * @return array containing 'id', 'name', 'date_created' and 'editable' or empty array on failure
     */
    function get($project_id){
        // Last project id
        $last_project_id = (new LastProjects())->get();
        // Get projects info in descending order
        if(@$stmt = $this->mysqli->prepare('SELECT project_id, project_name, CONVERT_TZ(date_created,\'SYSTEM\',\'UTC\') FROM `projects` WHERE `user_id` = ? AND `project_id` = ?')){
            $stmt->bind_param('ii', $_SESSION['user_id'], $project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0){
                $project = ['id' => null, 'name' => null, 'date_created' => null, 'editable' => false, 'pending' => false];

                $stmt->bind_result($project['id'], $project['name'], $project['date_created']);
                $stmt->fetch();
                // If a project id matches with the last project id and is not pending, the project is editable
                if($project['id'] == $last_project_id) $project['editable'] = true;
                if((new PendingProjects($project['id']))->isA()){
                    $project['pending'] = true;
                    $project['editable'] = false;
                }
                return $project;
            }
        }
        return [];
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
                $file['name'] = FileManager::SPECIES_RELATION;
                $file['path'] = Config::PROJECT_DIRECTORY . '/' . $project_id . '/' . FileManager::SPECIES_RELATION;
                break;
            case Constants::EXPORT_DISTANT_MATRIX:
                $file['mime'] = 'text/plain';
                $file['name'] = 'DistanceMatrix.txt';
                $file['path'] = Config::PROJECT_DIRECTORY . '/' . $project_id . '/' . FileManager::DISTANT_MATRIX_FORMATTED;
                break;
            case Constants::EXPORT_NEIGHBOUR_TREE:
                $file['mime'] = 'image/jpeg';
                $file['name'] = FileManager::NEIGHBOUR_TREE;
                $file['path'] = Config::PROJECT_DIRECTORY . '/' . $project_id . '/' . FileManager::NEIGHBOUR_TREE;
                break;
            case Constants::EXPORT_UPGMA_TREE:
                $file['mime'] = 'image/jpeg';
                $file['name'] = FileManager::UPGMA_TREE;
                $file['path'] = Config::PROJECT_DIRECTORY . '/' . $project_id . '/' . FileManager::UPGMA_TREE;
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
                $zip->addFile($project_dir . '/' . FileManager::SPECIES_RELATION, '/' . FileManager::SPECIES_RELATION);
                $zip->addFile($project_dir . '/' . FileManager::DISTANT_MATRIX_FORMATTED, '/DistanceMatrix.txt');
                $zip->addFile($project_dir . '/' . FileManager::NEIGHBOUR_TREE, '/' . FileManager::NEIGHBOUR_TREE);
                $zip->addFile($project_dir . '/' . FileManager::UPGMA_TREE, '/' . FileManager::UPGMA_TREE);
                $zip->addFile($project_dir . '/' . FileManager::CONFIG_JSON, '/' . FileManager::CONFIG_JSON);
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
     * FIXME: should also delete the uploaded files
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
        $isAPendingProject = $pendingProject->isA();
        if($isAPendingProject){
            $pendingProject->cancel();
            $pendingProject->remove();
        }
        // Delete project from the database
        if(@$stmt = $this->mysqli->prepare('DELETE FROM `projects` WHERE `user_id` = ? AND `project_id` = ?')){
            $stmt->bind_param('ii', $user_id, $project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE){
                // Delete the project files
                if($isAPendingProject) exec('rm -rf "' . Config::WORKING_DIRECTORY . '/Projects/' . $project_id . '"');
                else exec('rm -rf "' . Config::PROJECT_DIRECTORY . '/' . $project_id . '"');
                // Delete was a success
                return Constants::PROJECT_DELETE_SUCCESS;
            }else return Constants::PROJECT_DOES_NOT_EXIST;
        }
        return Constants::PROJECT_DELETE_FAILED;
    }

    function getType(){
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $this->_get_user_id();
        if((new PendingProjects($this->_project_id, $user_id))->isA()) return self::PENDING_PROJECT;
        else if((new LastProjects($user_id))->isA($this->_project_id)) return self::LAST_PROJECT; // same as self::NEW_PROJECT
        else return self::REGULAR_PROJECT;
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
        $user_id = $_SESSION['user_id'];
        if(@$stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM projects WHERE user_id = ? AND project_id = ?')){
            $stmt->bind_param('ii', $user_id, $project_id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            if($count == Constants::COUNT_ONE) return true;
        }
        return false;
    }

    /**
     * May be in a separate class
     */

    function unread_projects_info(){
        $user_id = $_SESSION['user_id'];
        $projects = [];
        $pending_projects = (new PendingProjects())->getAll(true);
        if(@$stmt = $this->mysqli->prepare('SELECT project_id, project_name, CONVERT_TZ(date_created,\'SYSTEM\',\'UTC\') FROM projects WHERE user_id = ? AND seen = 0 ORDER BY project_id DESC')){
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->store_result();
            for($i = 0; $i < $stmt->num_rows; ++$i){
                $project = [];
                $stmt->bind_result($project['id'], $project['name'], $project['date_created']);
                $stmt->fetch();
                if(!in_array($project['id'], $pending_projects)) array_push($projects, $project);
            }
        }
        return $projects;
    }

    function set_seen($project_id){
        $this->_project_id = $project_id;
        if($this->getType() == self::PENDING_PROJECT) return false;
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
    private function _get_user_id(){
        if($stmt = $this->mysqli->prepare('SELECT user_id FROM projects WHERE project_id = ?')){
            $stmt->bind_param('i', $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $stmt->bind_result($user_id);
                $stmt->fetch();
                return $user_id;
            }
        }
        return null;
    }
}
