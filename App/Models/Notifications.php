<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/29/17
 * Time: 6:25 PM
 */

namespace ADACT\App\Models;


class Notifications extends Model{
    function getAll(){
        $user_id = $_SESSION['user_id'];
        $projects = [];
        if(@$stmt = $this->mysqli->prepare('SELECT a.project_id, a.project_name, CONVERT_TZ(a.date_created,\'SYSTEM\',\'UTC\') AS date_created FROM projects AS a LEFT OUTER JOIN pending_projects AS b ON a.project_id = b.project_id WHERE a.user_id = ? AND a.seen IS FALSE AND b.project_id IS NULL ORDER BY a.project_id DESC')){
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->store_result();
            for($i = 0; $i < $stmt->num_rows; ++$i){
                $project = [];
                $stmt->bind_result($project['id'], $project['name'], $project['date_created']);
                $stmt->fetch();
                array_push($projects, $project);
            }
        }
        return $projects;
    }

    function set_seen($project_id){
        if(@$stmt = $this->mysqli->prepare('UPDATE projects SET seen = TRUE WHERE project_id = ? AND user_id = ?')){
            $stmt->bind_param('ii', $project_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1){
                return true;
            }
        }
        return false;
    }
}
