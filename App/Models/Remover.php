<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 1/24/18
 * Time: 4:57 PM
 */

namespace ADACT\App\Models;

/**
 * Class Remover.
 *
 * Performs different types of deletion based on current status or other privileges.
 * Different types of deletion and their interpretations:
 * <table>
 * <thead>
 *  <tr>
 *      <th>Type constants</th>
 *      <th>Meaning</th>
 *  </tr>
 * </thead>
 * <tbody>
 *  <tr>
 *      <th>R_DELETE_ALL</th>
 *      <td>Delete everything, no information is kept.</td>
 *  </tr>
 *  <tr>
 *      <th>R_DELETE_FILES</th>
 *      <td>Delete only project files and folders, not anything stored in the DB.</td>
 *  </tr>
 *  <tr>
 *      <th>R_DELETE_INPUT</th>
 *      <td>Delete the input and the generated files (Files section), only keep output files.</td>
 *  </tr>
 *  <tr>
 *      <th>R_KEEP_ALL</th>
 *      <td>Keep everything, delete nothing.</td>
 *  </tr>
 *  <tr>
 *      <th>R_NOT_APPLICABLE</th>
 *      <td>Not applicable.</td>
 *  </tr>
 * </tbody>
 * </table>
 *
 * @package ADACT\App\Models
 */
class Remover extends ProjectPrivilegeHandler{ // has_a
    /* Delete related constants */
    /** Delete everything, no information is kept. */
    const R_DELETE_ALL   = 1;
    /** Delete only project files and folders, not anything stored in the DB. */
    const R_DELETE_FILES = 2;
    /** Delete the input and the generated files (Files section), only keep output files. */
    const R_DELETE_INPUT = 3;
    /** Keep everything, delete nothing. */
    const R_KEEP_ALL     = 4;
    /** Not applicable. */
    const R_NOT_APPLICABLE = 5;

    /**
     * Remover constructor.
     * @param int $project_id
     * @param int $user_id
     */
    public function __construct($project_id, $user_id = null){
        parent::__construct($project_id);
    }

    /**
     * Take necessary action innocently, ie. w/o disturbing anything
     *
     * @return int Returns the result type (R_ prefixed) constant
     */
    public function innocentlyRemove(){
        switch ($this->getResultType()){
            case self::RT_CANCELLED:
                $pending = new PendingProjects($this->_project_id);
                if($this->getEditMode() !== self::EM_INIT_FROM_INIT AND $pending->getStatus() < PendingProjects::PROJECT_TAKE_CARE){
                    // The project is being edited
                    // Revert back
                    $this->_delete_input_files();
                    $pending->cancel(false);
                    return self::R_DELETE_INPUT;
                }else{
                    // The project is not reversible
                    // Delete files and folders but not anything of the DB
                    if(!(new FileManager($this->_project_id))->self_destruct())
                        error_log("Couldn't delete project folder or it doesn't exist!");
                    return self::R_DELETE_FILES;
                }
            case self::RT_PENDING:
                // Not applicable
                return self::R_NOT_APPLICABLE;
            case self::RT_SUCCESS:
                if((new LastProjects())->isA($this->_project_id)){
                    // No action, keep everything
                    return self::R_KEEP_ALL; // NOTE: a deliberate fallthrough could be possible, but ignored
                }else{
                    // Delete PROJECT_DIRECTORY/{project_id}/Files
                    $this->_delete_input_files();
                    return self::R_DELETE_INPUT;
                }
            default: // RT_FAILED
                // No action, keep everything
                return self::R_KEEP_ALL;
        }
    }

    /**
     * Remove project
     * @param bool $force
     * @return int
     */
    public function remove($force = true){
        if($force){ // Delete files forcefully
            $this->_delete();
            return self::R_DELETE_ALL;
        }else{
            return $this->innocentlyRemove();
        }
    }

    /**
     * Delete a project permanently
     *
     * @return void
     */
    private function _delete(){
//        $lastProject = new LastProjects($this->_user_id);
//        if($lastProject->isA($this->_project_id)) $lastProject->remove();
//        // Delete as pending project if it is in the list
//        $pendingProject = new PendingProjects($this->_project_id, $this->_user_id);
//        $isAPendingProject = $pendingProject->isA();
//        if($isAPendingProject){
//            $pendingProject->cancel();
//            $pendingProject->remove();
//        }
//        // Delete project from the database
//        if(@$stmt = $this->mysqli->prepare('DELETE FROM projects WHERE user_id = ? AND project_id = ?')){
//            $stmt->bind_param('ii', $this->_user_id, $this->_project_id);
//            $stmt->execute();
//            $stmt->store_result();
//            if($stmt->affected_rows == 1){
//                // Delete the project files
//                if(!(new FileManager($this->_project_id))->self_destruct())
//                    error_log("Couldn't delete project folder or it doesn't exist!");
//            }else return self::PROJECT_DOES_NOT_EXIST;
//        }
    }

    private function _delete_input_files(){
        // Delete only the generated or input files, keep the output files
        // ie, delete the PROJECT_DIRECTORY/{project_id}/Files folder
        $project_dir = self::PROJECT_DIRECTORY . '/' . $this->_project_id . '/Files';
        if(file_exists($project_dir)) exec("rm -Rf \"{$project_dir}\"");
    }
}