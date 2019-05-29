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

use ADACT\App\Models\FileException;
use ADACT\App\Models\Logger;
use ADACT\App\Models\PendingProjects;
use ADACT\App\Models\ProjectProcess;

// Get all the pending projects
$pending_projects = (new PendingProjects())->getAll();
$logger = new Logger(__DIR__ . "/../logs/process.log");

if($pending_projects !== false AND count($pending_projects) > 0) {
    $logger->log("Running " . count($pending_projects) . " process(es)", Logger::GREEN)->flush();
    foreach ($pending_projects as $project) {
        try {
            (new ProjectProcess($project['id'], $project['user']))->init();
        } catch (FileException $e) {
            $logger->log($e->getMessage(), Logger::RED)->flush();
        } catch (phpmailerException $e) {
            $logger->log($e->getMessage(), Logger::RED)->flush();
        }
    }
}
//else{
//    $logger->log("Nothing to process at this time.", Logger::RED)->flush();
//}
exit(0);
