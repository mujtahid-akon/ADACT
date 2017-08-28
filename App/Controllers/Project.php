<?php

namespace AWorDS\App\Controllers;

use \AWorDS\App\Constants;
use AWorDS\App\HttpStatusCode;
use \AWorDS\Config;

class Project extends Controller{
    /**
     * all_projects method
     *
     * Prints all the project for the current user
     * or redirect to home, if not logged in
     */
    function all_projects(){
        $this->set_model();
        $logged_in = $this->{$this->_model}->login_check();
        $this->set('logged_in', $logged_in);
        $this->set('active_tab', 'projects');
        if($logged_in){
            $this->set('projects', $this->{$this->_model}->all_projects());
        }else $this->_redirect = true;
    }
    
    function delete_project(){
        $this->_JSON = true;
        $this->_JSON_contents['status'] = Constants::PROJECT_DELETE_FAILED;
        if(isset($this->_url_params['project_id'])) $project_id = $this->_url_params['project_id'];
        else exit();
        
        $this->set_model();
        $logged_in = $this->{$this->_model}->login_check();
        if($logged_in){
            $this->_JSON_contents['status'] = $this->{$this->_model}->delete_project($project_id);
        }
    }

    /**
     * download_project method.
     *
     * Downloads a project as a zip file
     */
    function download_project(){
        $this->_GUI = false;
        if(isset($this->_url_params['project_id'])) $project_id = $this->_url_params['project_id'];
        else exit();
        
        $this->set_model();
        $logged_in = $this->{$this->_model}->login_check();
        if($logged_in){
            $file = $this->{$this->_model}->export($project_id, Constants::EXPORT_ALL);
            if($file != null){
                header('Content-Type: ' . $file['mime']);
                header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
                readfile($file['path']);
            }else{
                $this->_redirect = true;
                $this->_redirect_location = 'projects';
            }
        }else $this->_redirect = true;
    }

    /**
     * last_project method
     *
     * Load the last project of the current user
     */
    function last_project(){
        $this->set_model();
        $logged_in = $this->{$this->_model}->login_check();
        if($logged_in){
            $last_project_id = $this->{$this->_model}->last_project_id();
            if($last_project_id != null){
                $this->_redirect = true;
                $this->_redirect_location = $last_project_id;
            }else{ // 404 Error
                $this->set('status', HttpStatusCode::NOT_FOUND);
            }
        }else $this->_redirect = true;
    }
    
    function new_project(){
        extract($this->_params);
        /**
         * @var string $config A JSON string containing all configurations
         */
        $this->set_model();
        $logged_in = $this->{$this->_model}->login_check();
        $this->_JSON = true;
        if($logged_in && $config != null){
            $this->_JSON_contents['id'] = $this->{$this->_model}->new_project(json_decode(htmlspecialchars_decode($config), true));
        }
    }
    
    function new_project_page(){
        $this->set_model();
        $logged_in = $this->{$this->_model}->login_check();
        if(!$logged_in){
            $this->_redirect = true;
            exit();
        }
        $this->set('logged_in', $logged_in);
        $this->set('active_tab', 'new');
        $this->set('dissimilarity_index', $this->{$this->_model}->dissimilarity_index);
    }
    
    function file_upload(){
        $this->set_model();
        $logged_in = $this->{$this->_model}->login_check();
        $this->_JSON = true;
        if($logged_in && isset($_FILES['filef'])){
            $this->_JSON_contents = $this->{$this->_model}->file_upload($_FILES['filef']);
        }else $this->_JSON_contents['status'] = Constants::FILE_UPLOAD_FAILED;
    }

    /**
     * get method
     *
     * get Distance Matrix, Species Relation, UPGMA Tree, Neighbour Tree
     */
    function get(){
        extract($this->_url_params);
        /**
         * @var string $file_name
         * @var int    $project_id
         */
        $this->_GUI_load_view = false;
        if(!isset($file_name, $project_id)) goto redirect;
        $this->set_model();
        /**
         * @var \AWorDS\App\Models\Project $project
         */
        $project = $this->{$this->_model};
        $logged_in = $project->login_check();
        if($logged_in AND $project->verify_project($project_id)){
            $file_type = null;
            switch($file_name){
                case 'SpeciesRelation.txt': $file_type = Constants::EXPORT_SPECIES_RELATION; break;
                case 'DistantMatrix.txt'  : $file_type = Constants::EXPORT_DISTANT_MATRIX;   break;
                case 'NeighbourTree.jpg'  : $file_type = Constants::EXPORT_NEIGHBOUR_TREE;   break;
                case 'UPGMATree.jpg'      : $file_type = Constants::EXPORT_UPGMA_TREE;
            }
            if($file_type == null) goto redirect;
            $file = $this->{$this->_model}->export($project_id, $file_type);
            if($file == null){
                redirect:
                $this->_redirect = true;
                $this->_redirect_location = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : Config::WEB_DIRECTORY . 'projects';
                exit();
            }
            header('Content-Type: ' . $file['mime']);
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            readfile($file['path']);
            exit();
        }
        goto redirect;
    }

    function project_overview(){
        extract($this->_url_params);
        if(!isset($project_id)){
            $this->_redirect = true;
            exit();
        }

        $this->set_model();
        /**
         * @var \AWorDS\App\Models\Project $project
         */
        $project = $this->{$this->_model};
        $logged_in = $project->login_check();
        if($logged_in AND $project->verify_project($project_id)){
            $is_last_project_id = $project_id == $project->last_project_id();
            $this->set('logged_in', $logged_in);
            $this->set('project_id', $project_id);
            $this->set('active_tab', 'projects');
            $this->set('config', json_decode(file_get_contents(
                Config::PROJECT_DIRECTORY . '/' . $project_id
                . '/' . Constants::CONFIG_JSON), true));
            $this->set('is_last_project_id', $is_last_project_id);
            $this->set('dissimilarity_index', $project->dissimilarity_index);
            if($is_last_project_id){
                $project->set_last_project_seen();
            }
            exit();
        }else{
            $this->_redirect = true;
            exit();
        }
    }
}