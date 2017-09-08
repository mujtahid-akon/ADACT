<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/3/17
 * Time: 3:58 PM
 */

namespace AWorDS\App\Models;

use AWorDS\App\Constants;

class PendingProjects extends Model
{
    const PROJECT_SUCCESS = 0;
    const PROJECT_FAILURE = 1;
    const PROJECT_INIT    = 2;
    const PROJECT_FETCHING_FASTA = 3;
    const PROJECT_FINDING_AW = 4;
    const PROJECT_GENERATE_DM = 5;
    const PROJECT_GENERATE_PT = 6;
    const PROJECT_TAKE_CARE   = 7;
    const STATUS = [
        self::PROJECT_SUCCESS => "Success!",
        self::PROJECT_FAILURE => "Failed!",
        self::PROJECT_INIT    => "Initializing...",
        self::PROJECT_FETCHING_FASTA => "Fetching FASTA files...",
        self::PROJECT_FINDING_AW  => "Finding Absent Words...",
        self::PROJECT_GENERATE_DM => "Generating Distance Matrix...",
        self::PROJECT_GENERATE_PT => "Generating Phylogenic Trees",
        self::PROJECT_TAKE_CARE   => "Generating final results"
    ];

    private $project_id = null;
    private $user_id = null;

    function __construct($project_id = null, $user_id = null){
        parent::__construct();
        if($project_id !== null) $this->project_id = $project_id;
        if($user_id !== null) $this->user_id = $user_id;
    }

    /**
     * Add a project to the pending list
     *
     * @param int $project_id
     * @return bool
     */
    function add($project_id){
        $this->project_id = $project_id;
        $pending_project_code = self::PROJECT_INIT;
        if(@$stmt = $this->mysqli->prepare("INSERT INTO pending_projects(project_id, user_id, status_code) VALUE (?,?, ?)")){
            $stmt->bind_param('iii', $this->project_id, $_SESSION['user_id'], $pending_project_code);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows === Constants::COUNT_ONE) return true;
        }
        return false;
    }

    /**
     * Get project info by project id
     *
     * @param int $project_id
     * @return array|bool [id, status, status_code, cancel]
     */
    function get($project_id){
        $this->project_id = $project_id;
        $info = [
            'id' => null,
            'status_code' => null,
            'status' => null,
            'cancel' => 0
        ];
        if(@$stmt = $this->mysqli->prepare("SELECT project_id, status_code, cancel FROM pending_projects WHERE user_id = ? AND project_id = ?")){
            $stmt->bind_param('ii', $_SESSION['user_id'], $this->project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows === Constants::COUNT_ONE){
                $stmt->bind_result($info['id'], $info['status_code'], $info['cancel']);
                $stmt->fetch();
                $info['status'] = self::STATUS[$info['status_code']];
                $info['cancel'] = $info['cancel'] == 0 ? false : true;
                return $info;
            }
        }
        return false;
    }

    /**
     * Get all project info
     *
     * @param bool $idOnly Whether to return only a list of id
     * @return array|bool Similar to self::get() except that it returns a list of info
     */
    function getAll($idOnly = false){
        if($this->user_id === null){
            $this->user_id = $_SESSION['user_id'];
        }
        $info_list = [];
        if(@$stmt = $this->mysqli->prepare("SELECT project_id, status_code, cancel FROM pending_projects WHERE user_id = ?")){
            $stmt->bind_param('i', $this->user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows >= Constants::COUNT_ONE){
                for($i = 0; $i<$stmt->num_rows; ++$i){
                    $info = [
                        'id' => null,
                        'status_code' => null,
                        'status' => null,
                        'cancel' => 0
                    ];
                    $stmt->bind_result($info['id'], $info['status_code'], $info['cancel']);
                    $stmt->fetch();
                    $info['status'] = self::STATUS[$info['status_code']];
                    $info['cancel'] = $info['cancel'] == 0 ? false : true;
                    array_push($info_list, $idOnly ? $info['id'] : $info);
                }
                return $info_list;
            }
        }
        return false;
    }

    /**
     * Check if the given project id is a pending project
     *
     * @param int $project_id
     * @return bool
     */
    function isA($project_id){
        $this->project_id = $project_id;
        if(@$stmt = $this->mysqli->prepare("SELECT COUNT(*) FROM pending_projects WHERE user_id = ? AND project_id = ?")){
            $stmt->bind_param('ii', $_SESSION['user_id'], $project_id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            if($count === Constants::COUNT_ONE) return true;
        }
        return false;
    }

    /**
     * Cancel the pending project
     *
     * @param int $project_id
     * @return bool
     */
    function cancel($project_id){
        if(@$stmt = $this->mysqli->prepare('UPDATE pending_projects SET cancel = 1 WHERE project_id = ? AND user_id = ?')){
            $stmt->bind_param('ii', $project_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE) return true;
        }
        return false;
    }

    /**
     * Set status code for the pending project
     *
     * @param int $project_id
     * @param int $status_code
     * @return bool
     */
    function status($status_code, $project_id = null){
        if($project_id === null){
            $project_id = $this->project_id;
        }else{
            $this->user_id = $_SESSION['user_id'];
        }
        if($project_id === null and $this->user_id === null) return false;

        if(@$stmt = $this->mysqli->prepare('UPDATE pending_projects SET status_code = ? WHERE project_id = ? AND user_id = ?')){
            $stmt->bind_param('iii', $status_code, $project_id, $this->user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE) return true;
        }
        return false;
    }

    /**
     * Delete/Remove from the pending project
     *
     * NOTE: This doesn't delete the entire project
     *
     * @param int|null $project_id Use the project id
     * @return bool
     */
    function remove($project_id = null){
        if($project_id == null){
            $project_id = $this->project_id;
        }else{
            $this->user_id = $_SESSION['user_id'];
        }
        if($project_id == null) return false;

        if(@$stmt = $this->mysqli->prepare('DELETE FROM pending_projects WHERE project_id = ? AND user_id = ?')){
            $stmt->bind_param('ii', $project_id, $this->user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE) return true;
        }
        return false;
    }
}