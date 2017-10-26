<?php

namespace AWorDS\App\Controllers\API;

use \AWorDS\App\Constants;
use AWorDS\App\HttpStatusCode;
use AWorDS\App\Models\FileUploader;
use AWorDS\App\Models\LastProjects;
use AWorDS\App\Models\Notifications;
use AWorDS\App\Models\PendingProjects;
use AWorDS\App\Models\ProjectConfig;
use \AWorDS\Config;

class Project extends API{
    /**
     * all_projects method
     *
     * Prints all the project for the current user
     * or redirect to home, if not logged in
     */
    function all_projects(){
        /**
         * @var \AWorDS\App\Models\Project $project
         */
        $project = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in){
            $this->set('logged_in', $logged_in);
            $this->set('active_tab', 'projects');
            $this->set('projects', $project->getAll());
        }else $this->redirect();
    }

    /**
     * result method.
     *
     * Downloads project(s) as a zip file
     */
    function result(){
        extract($this->get_params());
        /** @var string|array $project_ids A comma separated list of project IDs (eg. 1,2,3) */
        /** @var \AWorDS\App\Models\Project $project */
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
        }else $this->forbidden();
    }

    /**
     * last_project method
     *
     * Load the last project of the current user
     */
    function last_project(){
        /**
         * @var \AWorDS\App\Models\LastProjects $lastProject
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
        extract($this->get_params());
        $json = ['id' => null];
        /**
         * @var string $config A JSON string containing all configurations
         */
        /**
         * @var \AWorDS\App\Models\Project $project
         */
        $project = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in && $config != null){
            $project_id = $project->add(json_decode(htmlspecialchars_decode($config), true));
            $json['id'] = $project_id;
        }
        $this->json($json);
    }

    function file_upload(){
        $json = ['status' => FileUploader::FILE_UPLOAD_FAILED];
        /**
         * @var \AWorDS\App\Models\FileUploader $project
         */
        $project = $this->set_model('FileUploader');
        $logged_in = $project->login_check();
        if($logged_in && isset($_FILES['filef'])){
            $json = $project->upload($_FILES['filef']);
            if(is_array($json)){ // File uploaded successfully
                $json['status'] = FileUploader::FILE_UPLOAD_SUCCESS;
            }else{
                $json = ['status' => $json];
            }
        }
        $this->json($json);

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
        /** @var \AWorDS\App\Models\Project $project */
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
     * project_overview method
     *
     * Returns the overview of the current project
     */
    function project_overview(){
        extract($this->get_params());
        /** @var int $project_id */
        if(!isset($project_id)){
            $this->redirect();
            exit();
        }

        /** @var \AWorDS\App\Models\Project $project */
        $project = $this->set_model();
        $logged_in = $project->login_check();

        if($logged_in){
            if((string) ((int) $project_id) == $project_id AND $project->verify($project_id)){
                $this->set('logged_in', $logged_in);
                $this->set('project_id', $project_id);
                $this->set(Constants::ACTIVE_TAB, 'projects');
                $this->set('isTheLastProject', (new LastProjects())->isA($project_id));
                $this->set('dissimilarity_index', (new ProjectConfig())->dissimilarity_indexes);
                $this->set('isAPendingProject', (new PendingProjects())->isA($project_id));
                $this->set('project_info', $project->get($project_id));
                (new Notifications())->set_seen($project_id);
            }else{
                $this->redirect(Config::WEB_DIRECTORY . 'projects');
            }
        }else{
            $this->redirect();
        }
    }

    function pending_projects(){
        /** @var \AWorDS\App\Models\Project $project */
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
        $projects = [];
        /** @var \AWorDS\App\Models\Project $project */
        $project = $this->set_model('Project');
        if($project->login_check()){
            $pending_projects = (new PendingProjects())->getAll();
            if(isset($project_ids) && $project_ids != null){
                $project_ids = explode(',', $project_ids);
                foreach ($project_ids as $project_id){
                    $_project = $this->in_pending_list($pending_projects, $project_id);
                    if($_project == false){
                        $projectF = [
                            "id" => (int)$project_id,
                            "status" => [
                                "code" => 0,
                                "message" => PendingProjects::STATUS[PendingProjects::PROJECT_SUCCESS]
                            ]
                        ];
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
        }else $this->forbidden();

    }

    /**
     * delete method.
     *
     * Deletes the requested project(s)
     */
    function delete(){
        extract($this->get_params());
        /** @var string|array $project_ids A comma separated list of project IDs (eg. 1,2,3) */
        /** @var \AWorDS\App\Models\Project $project */
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
                        $projectF['status']['message'] = "Project does not exit.";
                        break;
                    }
                    array_push($projects, $projectF);
                }
                $this->set('projects', $projects);
                $this->status(HttpStatusCode::OK, "Success.");
            }else{
                $this->status(HttpStatusCode::BAD_REQUEST, "No project id is provided.");
            }
        }else $this->forbidden();
    }

    function cancel_process(){
        extract($this->get_params());
        $json = ['status' => Constants::PROJECT_DELETE_FAILED]; // Failed
        /** @var string $project_id A JSON string containing all configurations */
        /** @var \AWorDS\App\Models\Project $project */
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
        /** @var \AWorDS\App\Models\Project $project */
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
        /** @var \AWorDS\App\Models\Project $project */
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
        /** @var \AWorDS\App\Models\Project $project */
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
    private function forbidden(){
        $this->status(HttpStatusCode::FORBIDDEN, "You don't have the permission to access this.");
    }

    private function in_pending_list(&$projects, $project_id){
        foreach ($projects as &$project){
            if($project['id'] == $project_id) return $project;
        }
        return false;
    }
}