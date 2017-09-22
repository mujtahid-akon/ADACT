<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/7/17
 * Time: 5:04 PM
 */

require_once __DIR__ . '/../autoload.php';

// get all the pending projects
$pending_projects = (new \AWorDS\App\Models\PendingProjects())->getAll();

if($pending_projects !== false) {
    foreach ($pending_projects as $project) {
        (new \AWorDS\App\Models\Process($project['id'], $project['user']))->init();
    }
}else{
    null;
    //print date("[d M Y H:i:s]") . " No process at this time.\n";
}

exit(0);
