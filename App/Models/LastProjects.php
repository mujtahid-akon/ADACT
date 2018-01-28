<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/4/17
 * Time: 3:07 PM
 */

namespace ADACT\App\Models;

/**
 * Class LastProjects
 * @package ADACT\App\Models
 */
class LastProjects extends Model{
    private $user_id;

    function __construct($user_id = null){
        parent::__construct();
        $this->user_id = $user_id == null AND isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $user_id;
    }

    /**
     * Get the project_id of the last project
     *
     * @return int|null
     */
    function get(){
        if(@$stmt = $this->mysqli->prepare('SELECT `project_id` FROM `last_projects` WHERE `user_id` = ?')){
            $stmt->bind_param('i', $this->user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $stmt->bind_result($project_id);
                $stmt->fetch();
                return $project_id;
            }
        }
        return null;
    }

    /**
     * Set last project
     *
     * @param int $project_id
     * @return bool
     */
    function set($project_id){
        // Set the current project id as the last project id
        if(@$stmt = $this->mysqli->prepare('REPLACE INTO `last_projects` VALUE(?, ?)')){
            $stmt->bind_param('ii', $this->user_id, $project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows > 0) return true;
        }
        return false;
    }

    /**
     * Remove/Delete as last project
     *
     * - Delete project's temporary files
     * - Delete project_id from last_projects
     *
     * @return bool
     */
    function remove(){
        $last_project_id = $this->get();
        if(@$stmt = $this->mysqli->prepare('DELETE FROM last_projects WHERE user_id = ?')){
            $stmt->bind_param('i', $this->user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1){
                // Also delete the PROJECT_DIRECTORY/{last_project_id}/Files folder as
                // it is only intended for the last project
                (new Remover($last_project_id))->innocentlyRemove();
                return true;
            }
        }
        return false;
    }

    /**
     * Remove/Delete as last project
     *
     * - Delete project's temporary files
     * - Delete project_id from last_projects
     *
     * @param int $project_id
     * @return bool
     */
    function removeByID($project_id){
        if(@$stmt = $this->mysqli->prepare('DELETE FROM last_projects WHERE project_id = ?')){
            $stmt->bind_param('i', $project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1){
                // Also delete the PROJECT_DIRECTORY/{last_project_id}/Files folder as
                // it is only intended for the last project
                (new Remover($project_id))->innocentlyRemove();
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a project ID is the last project
     *
     * NOTE: This method has no user ID dependencies!
     *
     * @param int $project_id
     * @return bool
     */
    function isA($project_id){
        if(@$stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM last_projects WHERE project_id = ?')){
            $stmt->bind_param('i', $project_id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            if($count == 1) return true;
        }
        return false;
    }
}