<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/4/17
 * Time: 3:07 PM
 */

namespace ADACT\App\Models;


use ADACT\App\Constants;

class LastProjects extends Model
{
    private $project_id;
    private $user_id;

    function __construct($user_id = null){
        parent::__construct();
        $this->user_id = $user_id == null ? $_SESSION['user_id'] : $user_id;
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
            if($stmt->num_rows == Constants::COUNT_ONE){
                $stmt->bind_result($project_id);
                $stmt->fetch();
                $this->project_id = $project_id;
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
        $this->project_id = $project_id;
        // Delete the last project id provided they are not the same
        $last_project_id = $this->get();
        if($last_project_id != $project_id){
            $this->remove();
        }else return false;
        // Set the current project id as the last project id
        if(@$stmt = $this->mysqli->prepare('INSERT INTO `last_projects` VALUE(?, ?)')){
            $stmt->bind_param('ii', $this->user_id, $project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE) return true;
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
        if(@$stmt = $this->mysqli->prepare('DELETE FROM `last_projects` WHERE `user_id` = ?')){
            $stmt->bind_param('i', $this->user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE){
                // Also delete the PROJECT_DIRECTORY/{last_project_id}/Files folder as
                // it is only intended for the last project
                $project_dir = self::PROJECT_DIRECTORY . '/' . $last_project_id . '/Files';
                if(file_exists($project_dir)) exec("rm -R \"{$project_dir}\"");
                return true;
            }
        }
        return false;
    }

    function isA($project_id){
        $last_project_id = $this->get();
        return $project_id == $last_project_id;
    }
}