<?php

namespace ADACT\App\Controllers\API;

use ADACT\App\HttpStatusCode;
use ADACT\App\Models\FileUploader;
use ADACT\App\Models\Notifications;
use ADACT\App\Models\PendingProjects;
use \ADACT\Config;

class Project extends API{
    /**
     * all_projects method.
     */
    function all_projects(){
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model('Project');
        if($project->login_check()){
            $project_info = $project->getAll(true);
            $this->set('projects', $project_info);
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
            $this->set('projects', $project->getMultiple(explode(',', $project_ids), true));
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
                    $rt = (new PendingProjects($project_id))->getResultType();
                    if(!($project->verify($project_id) AND $rt === PendingProjects::RT_SUCCESS)){
                        $this->status(HttpStatusCode::UNPROCESSABLE_ENTITY, "One or more project IDs do not exist or were not successful.");
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
     * // TODO
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
                $this->redirect('projects/' . $last_project_id);
            }else{ // 404 Error
                $this->response(HttpStatusCode::NOT_FOUND);
                $this->set('status', HttpStatusCode::NOT_FOUND);
            }
        }else $this->redirect();
    }

    /**
     *
     */
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
     * // TODO
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
                case 'SpeciesRelation.txt': $file_type = $project::EXPORT_SPECIES_RELATION; break;
                case 'DistantMatrix.txt'  : $file_type = $project::EXPORT_DISTANCE_MATRIX;  break;
                case 'NeighbourTree.jpg'  : $file_type = $project::EXPORT_NEIGHBOUR_TREE;   break;
                case 'UPGMATree.jpg'      : $file_type = $project::EXPORT_UPGMA_TREE;
            }
            if($file_type == null) goto redirect;
            $file = $project->export($project_id, $file_type);
            if($file == null){
                redirect:
                $this->redirect('projects');
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

    /**
     * TODO
     */
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
            if(isset($project_ids)){ // A couple of project IDs are given
                $project_ids = explode(',', $project_ids);
                $projectF = [
                    "id" => null,
                    "status" => [
                        "code" => null,
                        "message" => null
                    ]
                ];
                foreach ($project_ids as $project_id){
                    $project_id = intval($project_id);
                    $projectF['id'] = $project_id;
                    if($project->verify($project_id)){
                        // Project exist for the given user
                        // Now check if it is pending
                        $pending_project = new PendingProjects($project_id);
                        $rt = $pending_project->getResultType();
                        switch ($rt){
                            case PendingProjects::RT_SUCCESS:
                                $projectF['status'] = [
                                    "code" => PendingProjects::PROJECT_SUCCESS,
                                    "message" => PendingProjects::STATUS[PendingProjects::PROJECT_SUCCESS]
                                ];
                                break;
                            case PendingProjects::RT_FAILED:
                                $projectF['status'] = [
                                    "code" => PendingProjects::PROJECT_FAILURE,
                                    "message" => PendingProjects::STATUS[PendingProjects::PROJECT_FAILURE]
                                ];
                                break;
                            case PendingProjects::RT_CANCELLED:
                                $projectF['status'] = [
                                    "code" => 8,
                                    "message" => "Project cancelled"
                                ];
                                break;
                            case PendingProjects::RT_PENDING:
                                $p_info = $pending_project->get();
                                $projectF['status'] = [
                                    "code"    => $p_info['status_code'],
                                    "message" => $p_info['status']
                                ];
                        }
                    }else{
                        // Project doesn't exist
                        $projectF['status'] = [
                            "code" => 404,
                            "message" => "Project does not exist!"
                        ];
                    }
                    array_push($projects, $projectF);
                }
            }else{ // All projects are requested
                $pending_projects = (new PendingProjects())->getAll();
                foreach ($pending_projects as $_project){
                    array_push($projects, [
                        "id" => $_project['id'],
                        "status" => [
                            "code" => $_project['status_code'],
                            "message" => $_project['status']
                        ]
                    ]);
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
                $projectF = [
                    "id" => null,
                    "status" => [
                        "code" => null,
                        "message" => null
                    ]
                ];
                foreach ($project_ids as $project_id){
                    $project_id = intval($project_id);
                    $projectF['id'] = $project_id;
                    switch($project->delete($project_id)){
                        case $project::PROJECT_DELETE_SUCCESS:
                            $projectF['status'] = [
                                "code" => HttpStatusCode::OK,
                                "message" => "Success!"
                            ];
                            break;
                        case $project::PROJECT_DELETE_FAILED:
                            $projectF['status'] = [
                                "code" => HttpStatusCode::INTERNAL_SERVER_ERROR,
                                "message" => "Failed!"
                            ];
                            break;
                        case $project::PROJECT_DOES_NOT_EXIST:
                            $projectF['status'] = [
                                "code" => HttpStatusCode::NOT_FOUND,
                                "message" => "Project does not exist."
                            ];
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

    /**
     *
     */
    function cancel_process(){
        extract($this->get_params());
        /** @var string|array $project_ids A comma separated list of project IDs (eg. 1,2,3) */
        if(!$this->_validate_projects($project_ids)){
            $this->handle_default();
            return;
        }
        $projects = [];
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model('Project');
        $logged_in = $project->login_check();
        if($logged_in){
            $project_ids = explode(',', $project_ids);
            $projectF = [
                "id" => null,
                "status" => [
                    "code" => null,
                    "message" => null
                ]
            ];
            foreach ($project_ids as $project_id){
                $project_id = intval($project_id);
                if($project->verify($project_id)){
                    $projectF['id'] = $project_id;
                    $pending_project = new PendingProjects($project_id);
                    if($pending_project->isA()){
                        if($pending_project->cancel()){
                            $projectF['status'] = [
                                "code" => HttpStatusCode::OK,
                                "message" => "Success!"
                            ];
                        }else{
                            $projectF['status'] = [
                                "code" => HttpStatusCode::INTERNAL_SERVER_ERROR,
                                "message" => "Failed!"
                            ];
                        }

                    }else{
                        $projectF['status'] = [
                            "code" => HttpStatusCode::PRECONDITION_FAILED,
                            "message" => "Not a pending project!"
                        ];
                    }
                }else{
                    $projectF['status'] = [
                        "code" => HttpStatusCode::NOT_FOUND,
                        "message" => "Project doesn't exit!"
                    ];
                }
                array_push($projects, $projectF);
            }
            if(count($projects) > 0){
                $this->status(HttpStatusCode::OK, "Success.");
                $this->set("projects", $projects);
            }else{
                $this->handle_default();
            }

        }else $this->_forbidden();
    }

    /**
     *
     */
    function edit_project(){
        extract($this->get_params());
        /**
         * @var string $project_id A JSON string containing all configurations
         * @var string $config
         */
        /** @var \ADACT\App\Models\Project $project */
        $project   = $this->set_model('Project');
        $logged_in = $project->login_check();
        if($logged_in){
            if($project->verify($project_id)){
                // config = $this->get_contents()
                $status = $project->edit(json_decode(htmlspecialchars_decode($this->get_contents()), true), $project_id);
                switch ($status){
                    case 0:
                        return $this->status(HttpStatusCode::OK, "Success!");
                    case HttpStatusCode::NOT_MODIFIED:
                        return $this->status(HttpStatusCode::NOT_MODIFIED, "No modification in the config file!");
                    case HttpStatusCode::INTERNAL_SERVER_ERROR:
                        return $this->status(HttpStatusCode::INTERNAL_SERVER_ERROR, "Failed!");
                    default:
                        return $this->status(HttpStatusCode::PRECONDITION_FAILED, "Project is not editable");
                }
            }else return $this->handle_default();
        }else return $this->_forbidden();
    }

    /**
     * get_unseen method.
     * todo
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

    /* Private functions */

    /**
     * forbidden method.
     */
    private function _forbidden(){
        $this->status(HttpStatusCode::FORBIDDEN, "You don't have the permission to access this.");
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
