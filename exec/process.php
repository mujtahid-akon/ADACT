<?php
/**
 * File: process.php
 *
 * Runs all the pending processes.
 *
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/7/17
 * Time: 5:04 PM
 */

require_once __DIR__ . '/../autoload.php';

use ADACT\App\Models\Logger;

// Get all the pending projects
$pending_projects = (new \ADACT\App\Models\PendingProjects())->getAll();
$logger = new Logger(__DIR__ . "/../logs/process.log");

if($pending_projects !== false AND count($pending_projects) > 0) {
    $logger->log("Running " . count($pending_projects) . " process(es)", Logger::GREEN)->flush();
    foreach ($pending_projects as $project) {
        (new \ADACT\App\Models\ProjectProcess($project['id'], $project['user']))->init();
    }
}
//else{
//    $logger->log("Nothing to process at this time.", Logger::RED)->flush();
//}
exit(0);
