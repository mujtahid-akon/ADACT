<?php

namespace ADACT\App\Controllers\API;

use \ADACT\App\Constants;
use ADACT\App\HttpStatusCode;
use ADACT\App\Models\FileUploader;
use ADACT\App\Models\LastProjects;
use ADACT\App\Models\Notifications;
use ADACT\App\Models\PendingProjects;
use ADACT\App\Models\ProjectConfig;
use \ADACT\Config;

class Project extends API{
    /**
     * all_projects method.
     */
    function all_projects(){
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model('Project');
        if($project->login_check()){
            $this->set('projects', $project->getAll());
            $this->status(HttpStatusCode::OK, "Success.");
        }else $this->_forbidden();
    }

    function get_projects(){
        extract($this->get_params());
        /** @var string $project_ids */
        if(!$this->_validate_projects($project_ids)){
            $this->handle_default();
            return;
        }
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model('Project');
        if($project->login_check()){
            $this->set('projects', $project->getMultiple(explode(',', $project_ids)));
            $this->status(HttpStatusCode::OK, "Success.");
        }else $this->_forbidden();
    }

    /**
     * result method.
     *
     * Downloads project(s) as a zip file
     */
    function result(){
        extract($this->get_params());
        /** @var string|array $project_ids A comma separated list of project IDs (eg. 1,2,3) */
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model('Project');
        if($project->login_check()){
            if($project_ids != null){
                $project_ids = explode(',', $project_ids);
                foreach ($project_ids as &$project_id){
                    $project_id = (int) $project_id;
                    if(!$project->verify($project_id)){
                        $this->status(HttpStatusCode::UNPROCESSABLE_ENTITY, "One or more project IDs do not exists.");
                        exit();
                    }
                }
                $file = $project->exportAll($project_ids);
                if($file !== null){
                    $this->load_view(false);
                    $this->response(HttpStatusCode::OK);
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="projects.zip"');
                    readfile($file);
                    exit();
                }
                $this->status(HttpStatusCode::INTERNAL_SERVER_ERROR, "Failed to get results.");
            }else{
                $this->status(HttpStatusCode::BAD_REQUEST, "No project id is provided.");
            }
        }else $this->_forbidden();
    }

    /**
     * last_project method
     *
     * Load the last project of the current user
     */
    function get_last_project(){
        /**
         * @var \ADACT\App\Models\LastProjects $lastProject
         */
        $lastProject = $this->set_model('LastProjects');;
        $logged_in = $lastProject->login_check();
        if($logged_in){
            $last_project_id = $lastProject->get();
            if($last_project_id != null){
                $this->redirect(Config::WEB_DIRECTORY . 'projects/' . $last_project_id);
            }else{ // 404 Error
                $this->response(HttpStatusCode::NOT_FOUND);
                $this->set('status', HttpStatusCode::NOT_FOUND);
            }
        }else $this->redirect();
    }
    
    function new_project(){
        $contents = $this->get_contents();
        if(empty($contents)){
            $this->handle_default();
            return;
        }
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model('Project');
        if($project->login_check()){
            $this->set('projects', $project->addMultiple($contents));
            $this->status(HttpStatusCode::OK, "Success!");
        }
    }

    /**
     * upload method.
     */
    function upload(){
        /**
         * @var \ADACT\App\Models\FileUploader $uploader
         */
        $uploader = $this->set_model('FileUploader');
        if($uploader->login_check()){
            if(isset($_FILES) && count($_FILES) > 0){
                $files = [];
                foreach ($_FILES as $file){
                    $status = $uploader->upload($file);
                    $status_code = (is_int($status) ? $status : $uploader::FILE_UPLOAD_SUCCESS);
                    $fileF = [
                        "name" => $file['name'],
                        "id"   => (is_int($status) ? null : $status['id']),
                        "data" => (is_int($status) ? null : $status['data']),
                        "status" => [
                            "code" => $status_code,
                            "message" => $this->_get_uploader_message($status_code)
                        ]
                    ];
                    array_push($files, $fileF);
                }
                $this->set('files', $files);
                $this->status(HttpStatusCode::OK, "Success.");
            }else{
                $this->status(HttpStatusCode::BAD_REQUEST, "No file to upload.");
            }
        }else $this->_forbidden();
    }

    /**
     * get_file method
     *
     * get Distance Matrix, Species Relation, UPGMA Tree, Neighbour Tree
     */
    function get_file(){
        extract($this->get_params());
        /**
         * @var string $file_name
         * @var int    $project_id
         */
        $this->load_view(false);
        if(!isset($file_name, $project_id)) goto redirect;
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in AND $project->verify($project_id)){
            $file_type = null;
            switch($file_name){
                case 'SpeciesRelation.txt': $file_type = Constants::EXPORT_SPECIES_RELATION; break;
                case 'DistantMatrix.txt'  : $file_type = Constants::EXPORT_DISTANT_MATRIX;   break;
                case 'NeighbourTree.jpg'  : $file_type = Constants::EXPORT_NEIGHBOUR_TREE;   break;
                case 'UPGMATree.jpg'      : $file_type = Constants::EXPORT_UPGMA_TREE;
            }
            if($file_type == null) goto redirect;
            $file = $project->export($project_id, $file_type);
            if($file == null){
                redirect:
                $this->redirect(isset($_SERVER['HTTP_REFERER']) ?
                    $_SERVER['HTTP_REFERER'] :
                    Config::WEB_DIRECTORY . 'projects');
                exit();
            }
            header('Content-Type: ' . $file['mime']);
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            readfile($file['path']);
            exit();
        }
        goto redirect;
    }

    /**
     * get_details method
     */
    function get_details(){
        extract($this->get_params());
        /** @var int $project_id */
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model('Project');
        if($project->login_check()){
            if(preg_match('/^\d+$/', $project_id)){
                if($project->verify($project_id)){
                    $this->set('project', $project->getDetails($project_id));
                    $this->status(HttpStatusCode::OK, "Success!");
                }else{
                    $this->status(HttpStatusCode::UNPROCESSABLE_ENTITY, "Invalid project ID");
                }
            }else $this->handle_default();
        }else $this->_forbidden();
    }

    function pending_projects(){
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in){
            $this->set('logged_in', $logged_in);
            $this->set('active_tab', 'projects');
            $this->set('projects', $project->getAllPending());
        }else $this->redirect();
    }

    /**
     * get_status method.
     *
     * Get the status of the pending projects or the given project IDs
     */
    function get_status(){
        extract($this->get_params());
        /** @var string|array $project_ids A comma separated list of project IDs (eg. 1,2,3) */
        if(isset($project_ids) && !$this->_validate_projects($project_ids)){
            $this->handle_default();
            return;
        }
        $projects = [];
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model('Project');
        if($project->login_check()){
            $pending_projects = (new PendingProjects())->getAll();
            if(isset($project_ids)){
                $project_ids = explode(',', $project_ids);
                foreach ($project_ids as $project_id){
                    $_project = $this->_in_pending_list($pending_projects, $project_id);
                    if($_project == false){
                        if($project->verify($project_id)){
                            $projectF = [
                                "id" => (int)$project_id,
                                "status" => [
                                    "code" => 0,
                                    "message" => PendingProjects::STATUS[PendingProjects::PROJECT_SUCCESS]
                                ]
                            ];
                        }else{
                            $projectF = [
                                "id" => (int)$project_id,
                                "status" => [
                                    "code" => 8,
                                    "message" => "Project does not exist!"
                                ]
                            ];
                        }
                    }else{
                        $projectF = [
                            "id" => $_project['id'],
                            "status" => [
                                "code" => $_project['status_code'],
                                "message" => $_project['status']
                            ]
                        ];
                    }
                    array_push($projects, $projectF);
                }
            }else{
                foreach ($pending_projects as $_project){
                    $projectF = [
                        "id" => $_project['id'],
                        "status" => [
                            "code" => $_project['status_code'],
                            "message" => $_project['status']
                        ]
                    ];
                    array_push($projects, $projectF);
                }
            }
            // Output
            if(count($projects) > 0){
                $this->status(HttpStatusCode::OK, "Success.");
                $this->set("projects", $projects);
            }else{
                $this->status(HttpStatusCode::NOT_FOUND, "No pending projects.");
            }
        }else $this->_forbidden();
    }

    /**
     * delete method.
     *
     * Deletes the requested project(s)
     */
    function delete(){
        extract($this->get_params());
        /** @var string|array $project_ids A comma separated list of project IDs (eg. 1,2,3) */
        if(!$this->_validate_projects($project_ids)){
            $this->handle_default();
            return;
        }
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model('Project');
        if($project->login_check()){
            if($project_ids != null){
                $projects = [];
                $project_ids = explode(',', $project_ids);
                foreach ($project_ids as $project_id){
                    $projectF = [
                        "id" => (int)$project_id,
                        "status" => [
                            "code" => null,
                            "message" => null
                        ]
                    ];
                    switch($project->delete((int)$project_id)){
                        case $project::PROJECT_DELETE_SUCCESS:
                            $projectF['status']['code'] = HttpStatusCode::OK;
                            $projectF['status']['message'] = "Success!";
                            break;
                        case $project::PROJECT_DELETE_FAILED:
                            $projectF['status']['code'] = HttpStatusCode::INTERNAL_SERVER_ERROR;
                            $projectF['status']['message'] = "Failed!";
                            break;
                        case $project::PROJECT_DOES_NOT_EXIST:
                        $projectF['status']['code'] = HttpStatusCode::NOT_FOUND;
                        $projectF['status']['message'] = "Project does not exist.";
                        break;
                    }
                    array_push($projects, $projectF);
                }
                $this->set('projects', $projects);
                $this->status(HttpStatusCode::OK, "Success.");
            }else{
                $this->status(HttpStatusCode::BAD_REQUEST, "No project id is provided.");
            }
        }else $this->_forbidden();
    }

    function cancel_process(){
        extract($this->get_params());
        $json = ['status' => Constants::PROJECT_DELETE_FAILED]; // Failed
        /** @var string $project_id A JSON string containing all configurations */
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in){
            if($project->verify($project_id)){
                $pending_project = new PendingProjects($project_id);
                if($pending_project->isA()){
                    $json['status'] = $project->delete($project_id); // 0 = SUCCESS
                }
            }
        }
        $this->json($json);
    }

    function fork_project(){
        extract($this->get_params());
        /** @var string $project_id A JSON string containing all configurations */
        /** @var \ADACT\App\Models\Project $project */
        $project   = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in){
            if($project->can_fork($project_id)){
                $_SESSION['forked_id'] = $project_id;
                $this->redirect('/projects/new');
                exit();
            }
            $this->redirect('/projects/'. $project_id);
            exit();
        }
        $this->redirect();
    }

    function edit_project(){
        extract($this->get_params());
        /**
         * @var string $project_id A JSON string containing all configurations
         * @var string $config
         */
        /** @var \ADACT\App\Models\Project $project */
        $project   = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in){
            $this->json();
            if($project->can_edit($project_id)){
                error_log($config);
                $this->set('status', 0);
            }else{
                //$this->redirect('/projects/'. $project_id);
                $this->set('status', 1);
            }
        }else $this->redirect();
    }

    /**
     * get_unseen method.
     */
    function get_unseen(){
        /** @var string $project_id A JSON string containing all configurations */
        /** @var \ADACT\App\Models\Project $project */
        $project   = $this->set_model();
        $logged_in = $project->login_check();
        $this->json();
        if($logged_in){
            $this->set("projects" ,(new Notifications())->getAll());
        }
    }

    /**
     * Private functions
     */

    /**
     * forbidden method.
     */
    private function _forbidden(){
        $this->status(HttpStatusCode::FORBIDDEN, "You don't have the permission to access this.");
    }

    /**
     * in_pending_list method.
     *
     * @param array $projects
     * @param int   $project_id
     * @return false|array
     */
    private function _in_pending_list(array &$projects, $project_id){
        foreach ($projects as &$project){
            if($project['id'] == $project_id) return $project;
        }
        return false;
    }

    /**
     * get_uploader_message method.
     *
     * @param int $status_code
     * @return string
     */
    private function _get_uploader_message($status_code){
        switch ($status_code){
            case FileUploader::FILE_UPLOAD_SUCCESS: return "Success!";
            case FileUploader::INVALID_FILE: return "Invalid file!";
            case FileUploader::INVALID_MIME_TYPE: return "Invalid mime type!";
            case FileUploader::SIZE_LIMIT_EXCEEDED: return "File size limit exceeded!";
            default: return "File upload failed!"; // Mimic
        }
    }

    private function _validate_projects($project_ids){
        return preg_match('/^\d+(,\d+)*$/', $project_ids) ? true : false;
    }
}
