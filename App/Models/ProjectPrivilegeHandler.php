<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 1/24/18
 * Time: 5:12 PM
 */

namespace ADACT\App\Models;

/**
 * Class PrivilegeHandler.
 *
 * Handles different types of privileges of a single project
 *
 * @package ADACT\App\Models
 */
class ProjectPrivilegeHandler extends Model implements ProjectPrivilegeHandlerInterface {
    /** @var int Project ID */
    protected $_project_id;

    /**
     * PrivilegeHandler constructor.
     * @param int $project_id Pre-existing project ID
     */
    public function __construct($project_id){
        parent::__construct();
        $this->_project_id = $project_id;
    }

    /**
     * Get project type of a project.
     * <table>
     * <thead>
     *  <tr>
     *      <td>Project types</td>
     *      <td>Meaning</td>
     *  </tr>
     * </thead>
     * <tbody>
     *  <tr>
     *      <td>PT_PENDING</td>
     *      <td>Pending project</td>
     *  </tr>
     *  <tr>
     *      <td>PT_NEW</td>
     *      <td>Project is new and was successfully processed</td>
     *  </tr>
     *  <tr>
     *      <td>PT_LAST</td>
     *      <td>Same as PT_NEW</td>
     *  </tr>
     *  <tr>
     *      <td>PT_REGULAR</td>
     *      <td>None of the above</td>
     *  </tr>
     * </tbody>
     * </table>
     *
     * @deprecated Use getResultType() instead
     * @return int
     */
    function getProjectType(){
        switch ($this->_get_result_type_from_db()){
            case self::RT_PENDING:
                return self::PT_PENDING;
            case self::RT_SUCCESS: // also, PT_REGULAR
                if($this->isEditable()) return self::PT_NEW; // = self::PT_LAST
                else return self::PT_REGULAR; // fallthrough is a possibility
            default: // otherwise
                return self::PT_REGULAR;
        }
    }

    function isEditable(){
        return (new LastProjects())->isA($this->_project_id);
    }

    /**
     * Returns the edit mode for a project.
     * Edit mode is null when it fulfills one of the following criteria:
     * - status_code = PendingProjects::PROJECT_FAILURE AND cancel = FALSE (meaning a failed/cancelled project)
     * - Other failures
     *
     * @return int|null EM_INIT_FROM_INIT|EM_INIT_FROM_AW|EM_INIT_FROM_DM
     */
    function getEditMode(){
        if(@$stmt = $this->mysqli->prepare("SELECT edit_mode FROM pending_projects WHERE project_id = ? AND cancel = FALSE AND (status_code != " . PendingProjects::PROJECT_FAILURE . " OR status_code != " . PendingProjects::PROJECT_SUCCESS . ")")){
            $stmt->bind_param('i', $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows === 1){
                $stmt->bind_result($edit_mode);
                $stmt->fetch();
                return $edit_mode;
            }
        }
        return null;
    }

    /**
     * Get result type of a project.
     * Priority list: (when scanning into the pending_projects table)
     * 1. Cancel
     * 2. Failed
     * 3. Success
     * 4. Pending
     *
     * If pending_projects table doesn't contain the project ID, it is assumed that
     * the project exists and is successfully executed. So, necessary sanitizations
     * must be done in the program where this function is called to ensure the
     * existence of the project ID.
     *
     * Explicitly setting status code will simulate the results
     * instead of providing the true results from DB.
     * @param int  $status_code
     * @param bool $cancel
     * @return int RT_ prefixed constants
     */
    function getResultType($status_code = null, $cancel = null){
        return ($status_code === null OR $cancel === null) ? $this->_get_result_type_from_db() : $this->_get_result_type($status_code, $cancel);
    }

    private function _get_result_type_from_db(){
        if(@$stmt = $this->mysqli->prepare("SELECT status_code, cancel FROM pending_projects WHERE project_id = ?")){
            $stmt->bind_param('i', $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows === 1){
                $stmt->bind_result($status_code, $cancel);
                $stmt->fetch();
                $cancel = $cancel === 1 ? true : false;
                return $this->_get_result_type($status_code, $cancel);
            }
        }
        return self::RT_SUCCESS;
    }

    private function _get_result_type($status_code, $cancel){
        if($cancel === true){
            return self::RT_CANCELLED;
        }else{
            switch ($status_code){
                case PendingProjects::PROJECT_FAILURE:
                    return self::RT_FAILED;
                case PendingProjects::PROJECT_SUCCESS:
                    return self::RT_SUCCESS;
                default:
                    return self::RT_PENDING;
            }
        }
    }
}