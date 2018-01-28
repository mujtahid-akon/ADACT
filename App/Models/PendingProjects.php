<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/3/17
 * Time: 3:58 PM
 */

namespace ADACT\App\Models;

/**
 * Class PendingProjects
 *
 * Deals with pending projects and their processes. Here's a description of
 * how this class works:
 * - Adds or removes a pending project
 * - Gets information of a single or all pending projects
 * - Handles edit modes: see PendingProjects::getEditMode() for technical details
 *      * Edit modes are set only when the project is active, running or cancelled
 *      * Edit mode is set to null if the project has failed and is not cancelled
 * - Handles project status
 *      * Project status are generated according to the current stage of processing
 * - Cancels a project: see PendingProjects::cancel() for technical details
 *      * Canceling a project has superiority over project status, even edit modes
 *
 * Here's a simplified version:
 * <table>
 * <thead>
 * <tr>
 *  <th>Status constants</th>
 *  <th>Cancel values</th>
 *  <th>Interpretation</th>
 * </tr>
 * </thead>
 * <tbody>
 * <tr>
 *  <td>PROJECT_INIT</td>
 *  <td>FALSE</td>
 *  <td>active</td>
 * </tr>
 * <tr>
 *  <td>PROJECT_FETCHING_FASTA</td>
 *  <td rowspan=5>FALSE</td>
 *  <td rowspan=5>running</td>
 * </tr>
 * <tr>
 *  <td>PROJECT_FINDING_AW</td>
 * </tr>
 * <tr>
 *  <td>PROJECT_GENERATE_DM</td>
 * </tr>
 * <tr>
 *  <td>PROJECT_GENERATE_PT</td>
 * </tr>
 * <tr>
 *  <td>PROJECT_TAKE_CARE</td>
 * </tr>
 * <tr>
 *  <td>PROJECT_FAILURE</td>
 *  <td>FALSE</td>
 *  <td>failed</td>
 * </tr>
 * <tr>
 *  <td>all of above</td>
 *  <td>TRUE</td>
 *  <td>cancelled</td>
 * </tr>
 * <tr>
 *  <td>PROJECT_SUCCESS</td>
 *  <td>-</td>
 *  <td>success</td>
 * </tr>
 * </tbody>
 * </table>
 *
 * Behaviours of the interpretations:
 * <table>
 * <thead>
 * <tr>
 *  <th>Title</th>
 *  <th>Behaviours</th>
 * </tr>
 * </thead>
 * <tbody>
 * <tr>
 *  <td>active</td>
 *  <td rowspan=2>
 *      Typical or the most general behaviours, ie. project is queued or in a certain step
 *      during the processing period
 *  </td>
 * </tr>
 * <tr>
 *  <td>running</td>
 * </tr>
 * <tr>
 *  <td>cancelled</td>
 *  <td>
 *      The project is cancelled, meaning that the project is to be deleted permanently
 *      with absolutely no way to recover it again
 *  </td>
 * </tr>
 * <tr>
 *  <td>failed</td>
 *  <td>
 *      The project has failed during the processing period. This is to be handled with
 *      special care to determine the step where it failed to find a valid reason for
 *      the failure
 *  </td>
 * </tr>
 * <tr>
 *  <td>success</td>
 *  <td>
 *      The project has been successfully executed. This may or may not be present in the
 *      pending_projects table
 *  </td>
 * </tr>
 * </tbody>
 * </table>
 *
 * NOTE: Interpretations are handled in the ADACT\App\Models\Process class
 *
 * @package ADACT\App\Models
 * @see ProjectProcess - All the processes are handled here.
 */
class PendingProjects extends ProjectPrivilegeHandler{ // has_a
    /* Constants related to processing the pending projects: DON'T MODIFY THESE CONSTANTS IN ANY WAY!!! */
    /** Processing the project was successful: a bit useless constant :) */
    const PROJECT_SUCCESS = 0;
    /** Processing the project failed (sadly) */
    const PROJECT_FAILURE = 1;
    /** Process is queued and (hopefully) will be started processing within a minute */
    const PROJECT_INIT    = 2;
    /** Process is running, and currently it's fetching FASTA files */
    const PROJECT_FETCHING_FASTA = 3;
    /** Process is running, and now it's finding absent words (MAW or RAW) */
    const PROJECT_FINDING_AW  = 4;
    /** Process is running, and now it's generating distance matrix */
    const PROJECT_GENERATE_DM = 5;
    /** Process is running, and now it's generating phylogenetic trees */
    const PROJECT_GENERATE_PT = 6;
    /** Process is running, and now it's generating final results */
    const PROJECT_TAKE_CARE   = 7;
    /** Status message: the interpreter */
    const STATUS = [
        self::PROJECT_SUCCESS => "Success!",
        self::PROJECT_FAILURE => "Failed!",
        self::PROJECT_INIT    => "Queued",
        self::PROJECT_FETCHING_FASTA => "Fetching FASTA files...",
        self::PROJECT_FINDING_AW  => "Finding Absent Words...",
        self::PROJECT_GENERATE_DM => "Generating Distance Matrix...",
        self::PROJECT_GENERATE_PT => "Generating Phylogenetic Trees...",
        self::PROJECT_TAKE_CARE   => "Generating final results..."
    ];

    /** @var int|null The current user ID */
    protected $_user_id;
    /** @var int|null The status constant */
    protected $_status;

    /**
     * PendingProjects constructor.
     * @param int|null $project_id
     * @param int|null $user_id
     */
    function __construct($project_id = null, $user_id = null){
        parent::__construct($project_id);
        $this->_user_id = ($user_id === null AND isset($_SESSION)) ? $_SESSION['user_id'] : $user_id;
        $this->_status  = $this->_get_status();
    }

    /**
     * Add a project to the pending list
     *
     * @param int $project_id
     * @param int $edit_mode PendingProjects::NEW_PROJECT|PendingProjects::EDIT_PROJECT
     * @return bool
     */
    function add($project_id, $edit_mode = self::EM_INIT_FROM_INIT){
        $this->_project_id = $project_id;
        $pending_project_code = self::PROJECT_INIT;
        if(@$stmt = $this->mysqli->prepare("REPLACE INTO pending_projects(project_id, user_id, status_code, edit_mode) VALUE (?,?,?,?)")){
            $stmt->bind_param('iiii', $this->_project_id, $this->_user_id, $pending_project_code, $edit_mode);
            $stmt->execute();
            $stmt->store_result();
            // affected rows can be 1 or 2 (based on insert or modify)
            if($stmt->affected_rows > 0) return true;
        }
        return false;
    }

    /**
     * Get project info by project id
     *
     * @param int $project_id
     * @return array|bool [id, status, status_code, cancel]
     */
    function get($project_id = null){
        if($project_id !== null) $this->_project_id = $project_id;
        $info = [
            'id' => null,
            'status_code' => null,
            'status' => null,
            'cancel' => 0,
            'edit_mode' => null
        ];
        if(@$stmt = $this->mysqli->prepare("SELECT project_id, status_code, cancel, edit_mode FROM pending_projects WHERE project_id = ?")){
            $stmt->bind_param('i', $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows === 1){
                $stmt->bind_result($info['id'], $info['status_code'], $info['cancel'], $info['edit_mode']);
                $stmt->fetch();
                $info['status'] = self::STATUS[$info['status_code']];
                $info['cancel'] = $info['cancel'] == 0 ? false : true;
                return $info;
            }
        }
        return false;
    }

    /**
     * Get status code for the pending project
     *
     * @return int|null
     */
    function getStatus(){
        return $this->_status;
    }

    /**
     * Get all project info
     *
     * @param bool $idOnly Whether to return only a list of id
     * @return array|bool a list of [id, status, status_code, cancel] or only ids, or empty array
     */
    function getAll($idOnly = false){
        $info_list = [];
        $query = ($this->_user_id === null) ?
            "SELECT project_id, user_id, status_code, cancel, edit_mode FROM pending_projects" :
            "SELECT project_id, user_id, status_code, cancel, edit_mode FROM pending_projects WHERE user_id = ?";
        if(@$stmt = $this->mysqli->prepare($query)){
            if($this->_user_id !== null)
                $stmt->bind_param('i', $this->_user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows >= 1){
                for($i = 0; $i<$stmt->num_rows; ++$i){
                    $info = [
                        'id' => null,
                        'user' => null,
                        'status_code' => null,
                        'status' => null,
                        'cancel' => 0,
                        'edit_mode' => null
                    ];
                    $stmt->bind_result($info['id'], $info['user'],$info['status_code'], $info['cancel'], $info['edit_mode']);
                    $stmt->fetch();
                    $info['status'] = self::STATUS[$info['status_code']];
                    $info['cancel'] = $info['cancel'] === 1 ? true : false;
                    if($this->getResultType($info['status_code'], $info['cancel']) === Project::RT_PENDING) array_push($info_list, $idOnly ? $info['id'] : $info);
                }
                return $info_list;
            }
        }
        return [];
    }

    /**
     * Check if the given project id is a pending project
     *
     * @param int $project_id
     * @return bool
     */
    function isA($project_id = null){
        if($project_id !== null) $this->_project_id = $project_id;
        if(@$stmt = $this->mysqli->prepare("SELECT status_code, cancel FROM pending_projects WHERE project_id = ?")){
            $stmt->bind_param('i', $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows === 1){
                $stmt->bind_result($status_code, $cancel);
                $stmt->fetch();
                if($this->getResultType($status_code, $cancel) === self::RT_PENDING) return true;
            }
        }
        return false;
    }

    /**
     * Check whether a project is cancelled
     * @return bool
     */
    function isCancelled(){
        if(@$stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM pending_projects WHERE project_id = ? AND cancel = TRUE')){
            $stmt->bind_param('i', $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            if($count == 1) return true;
        }
        return false;
    }

    /**
     * Check whether the project has failed.
     * The condition for failure is:
     *  cancel = TRUE AND status_code = PendingProjects::PROJECT_FAILURE
     *
     * @return bool
     */
    function isFailed(){
        return (!$this->isCancelled() AND $this->_status === self::PROJECT_FAILURE);
    }

    /**
     * Cancels a pending project.
     * There can be two situations:
     * 1. If edit mode is PendingProjects::PROJECT_INIT_FROM_INIT,
     *      performs delete files with a warning and a notice after cancellation
     * 2. Else, halt the current execution and preserve the previously executed info if possible
     *
     * @param bool $value The value of cancel (default = true)
     * @return bool
     * @see ProjectProcess - Cancel project is handled here
     */
    function cancel($value = true){
        $value = $value ? 1 : 0;
        if($this->isCancelled()) return true;
        if(@$stmt = $this->mysqli->prepare('UPDATE pending_projects SET cancel = ? WHERE project_id = ?')){
            $stmt->bind_param('ii', $value, $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1) return true;
        }
        return false;
    }

    /**
     * Set status code for the pending project
     *
     * @param int $status_code
     * @return bool
     * @see ProjectProcess - Status is set from this class
     */
    function setStatus($status_code){
        if(@$stmt = $this->mysqli->prepare('UPDATE pending_projects SET status_code = ? WHERE project_id = ?')){
            $stmt->bind_param('ii', $status_code, $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1){
                $this->_status = $status_code;
                return true;
            }
        }
        return false;
    }

    /**
     * Delete/Remove from the pending project list
     *
     * NOTE: This doesn't delete the project itself
     *
     * @return bool
     */
    function remove(){
        if(@$stmt = $this->mysqli->prepare('DELETE FROM pending_projects WHERE project_id = ?')){
            $stmt->bind_param('i', $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1) return true;
        }
        return false;
    }

    /**
     * Get status for the pending project
     *
     * @return null|int
     */
    private function _get_status(){
        if(@$stmt = $this->mysqli->prepare('SELECT status_code FROM pending_projects WHERE project_id = ?')){
            $stmt->bind_param('i', $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows === 1){
                $stmt->bind_result($status);
                $stmt->fetch();
                return $status;
            }
        }
        return null;
    }
}
