<?php

namespace ADACT\App\Controllers;

use ADACT\App\HttpStatusCode;
use ADACT\App\Models\FileManager;
use ADACT\App\Models\FileUploader;
use ADACT\App\Models\LastProjects;
use ADACT\App\Models\Notifications;
use ADACT\App\Models\PendingProjects;
use ADACT\App\Models\ProjectConfig;

class Project extends Controller{
    /**
     * all_projects method
     *
     * Prints all the project for the current user
     * or redirect to home, if not logged in
     */
    function all_projects(){
        /**
         * @var \ADACT\App\Models\Project $project
         */
        $project = $this->set_model();
        if($project->user != null && $project->user['is_guest']){
            $this->redirect('');
            exit();
        }
        $logged_in = $project->login_check();
        if($logged_in){
            $this->set('logged_in', $logged_in);
            $this->set('active_tab', 'projects');
            $this->set('projects', $project->getAll());
            $this->set('is_guest', $project->user != null ? $project->user['is_guest'] : null);
        }else $this->redirect();
    }

    function delete_project(){
        $this->json();
        $this->set('status', \ADACT\App\Models\Project::PROJECT_DELETE_FAILED);

        if(isset($this->_url_params['project_id'])) $project_id = $this->_url_params['project_id'];
        else exit();
        
        /**
         * @var \ADACT\App\Models\Project $project
         */
        $project = $this->set_model();;
        $logged_in = $project->login_check();
        if($logged_in){
            $this->set('status', $project->delete($project_id));
        }
    }

    /**
     * download_project method.
     *
     * Downloads a project as a zip file
     */
    function download_project(){
        $this->_HTML = false;
        if(isset($this->_url_params['project_id'])) $project_id = $this->_url_params['project_id'];
        else exit();
        /**
         * @var \ADACT\App\Models\Project $project
         */
        $project = $this->set_model();;
        $logged_in = $project->login_check();
        if($logged_in){
            if((string) ((int) $project_id) == $project_id AND $project->verify($project_id)) {
                $file = $project->export($project_id, $project::EXPORT_ALL);
                if ($file != null) {
                    header('Content-Type: ' . $file['mime']);
                    header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
                    readfile($file['path']);
                } else $this->redirect('projects');
            }else $this->redirect('projects');
        }else $this->redirect();
    }

    /**
     * last_project method
     *
     * Load the last project of the current user
     */
    function last_project(){
        /**
         * @var LastProjects $lastProject
         */
        $lastProject = $this->set_model('LastProjects');;
        if($lastProject->user != null && $lastProject->user['is_guest']){
            $this->redirect('');
            exit();
        }
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
    
    function new_project(){
        extract($this->get_params());
        $json = ['id' => null];
        /**
         * @var string $config A JSON string containing all configurations
         */
        /**
         * @var \ADACT\App\Models\Project $project
         */
        $project = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in && $config != null){
            $project_id = $project->add(json_decode(htmlspecialchars_decode($config), true));
            $json['id'] = $project_id;
        }
        $this->json($json);
    }

    function new_project_page(){
        /**
         * @var \ADACT\App\Models\Project $project
         */
        $project = $this->set_model();
        $logged_in = $project->login_check();
        if(!$logged_in){
            $this->redirect();
            exit();
        }
        if(isset($_SESSION['forked_id'])){
            $project_id = $_SESSION['forked_id'];
            unset($_SESSION['forked_id']);
            if($project->verify($project_id)){
                $this->set('project_id', $project_id);
            }
        }
        $this->set('logged_in', $logged_in);
        $this->set('active_tab', 'new');
        $this->set('dissimilarity_index', (new ProjectConfig())->dissimilarity_indexes);
        $this->set('is_guest', $project->user != null ? $project->user['is_guest'] : null);
    }
    
    function file_upload(){
        $json = ['status' => FileUploader::FILE_UPLOAD_FAILED];
        /**
         * @var FileUploader $project
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

    function text_upload(){
        extract($this->get_params());
        /**
         * @var string $seq_text Sequence in FASTA format
         */
        $json = ['status' => FileUploader::FILE_UPLOAD_FAILED];
        /**
         * @var FileUploader $project
         */
        $project = $this->set_model('FileUploader');
        $logged_in = $project->login_check();
        if($logged_in && isset($seq_text)){
            $json = $project->text($seq_text);
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
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in AND $project->verify($project_id)){
            $file_type = null;
            switch($file_name){
                case FileManager::SPECIES_RELATION: $file_type = $project::EXPORT_SPECIES_RELATION; break;
                case FileManager::DISTANCE_MATRIX : $file_type = $project::EXPORT_DISTANCE_MATRIX;  break;
                case FileManager::NEIGHBOUR_TREE  : $file_type = $project::EXPORT_NEIGHBOUR_TREE;   break;
                case FileManager::UPGMA_TREE      : $file_type = $project::EXPORT_UPGMA_TREE;
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
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in){
            if((string) ((int) $project_id) == $project_id AND $project->verify($project_id)){
                $project_info = $project->get($project_id);
                if($project_info['result_type'] === PendingProjects::RT_CANCELLED)
                    $this->redirect('projects');
                $isPending = $project_info['result_type'] === PendingProjects::RT_PENDING;
                $this->set('logged_in', $logged_in);
                $this->set('project_id', $project_id);
                $this->set('active_tab', 'projects');
                $this->set('isTheLastProject', $project_info['last']);
                $this->set('dissimilarity_index', (new ProjectConfig())->dissimilarity_indexes);
                $this->set('isAPendingProject', $isPending);
                $this->set('project_info', $project_info);
                $this->set('is_guest', $project->user != null ? $project->user['is_guest'] : null);
                if(!$isPending) (new Notifications())->set_seen($project_id);
            }else{
                $this->redirect('projects');
            }
        }else{
            $this->redirect();
        }
    }

    function pending_projects(){
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model();
        if($project->user != null && $project->user['is_guest']){
            $this->redirect('');
            exit();
        }
        $logged_in = $project->login_check();
        if($logged_in){
            $this->set('logged_in', $logged_in);
            $this->set('active_tab', 'projects');
            $this->set('projects', $project->getAllPending());
            $this->set('is_guest', $project->user != null ? $project->user['is_guest'] : null);
        }else $this->redirect();
    }

    function status(){
        extract($this->get_params());
        $json = [];
        /** @var string $project_id A JSON string containing all configurations */
        $project = new \ADACT\App\Models\Project($project_id);
        $logged_in = $project->login_check();
        if($logged_in){
            $json = ['status_code' => PendingProjects::PROJECT_FAILURE, "status" => null];
            if($project->verify($project_id)){
                $result_type = $project->getResultType();
                $pending_project = new PendingProjects($project_id);
                if($result_type != PendingProjects::RT_SUCCESS){
                    $status = $pending_project->get();
                    $json['status_code'] = $status['status_code'];
                    $json['status']      = $status['status'];
                }else{
                    $json['status_code'] = $pending_project::PROJECT_SUCCESS;
                }
            }
        }
        $this->json($json);
    }

    function cancel_process(){
        extract($this->get_params());
        $json = ['status' => 1]; // Failed
        /** @var string $project_id A JSON string containing all configurations */
        /** @var \ADACT\App\Models\Project $project */
        $project = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in){
            if($project->verify($project_id)){
                $pending_project = new PendingProjects($project_id);
                if($pending_project->isA() AND $pending_project->cancel()){
//                    (new Remover($project_id, $_SESSION['user_id']))->innocentlyRemove();
                    $json['status'] = 0; // SUCCESS
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
                $this->redirect('projects/new');
                exit();
            }
            $this->redirect('projects/'. $project_id);
            exit();
        }
        $this->redirect();
    }

    function edit_project_page(){
        extract($this->get_params());
        /** @var string $project_id A JSON string containing all configurations */
        /** @var \ADACT\App\Models\Project $project */
        $project   = $this->set_model();
        $logged_in = $project->login_check();
        if($logged_in){
            if($project->can_edit($project_id)){
                $this->set('logged_in', $logged_in);
                $this->set('project_id', $project_id);
                $this->set('dissimilarity_index', (new ProjectConfig())->dissimilarity_indexes);
                $this->set('is_guest', $project->user != null ? $project->user['is_guest'] : null);
            }else{
                $this->redirect('projects/'. $project_id);
            }
        }else $this->redirect();
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
            $this->set('status', $project->edit(json_decode(htmlspecialchars_decode($config), true), $project_id));
        }else $this->redirect();
    }

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
}