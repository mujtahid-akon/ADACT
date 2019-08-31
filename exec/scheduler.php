<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/20/17
 * Time: 4:09 AM
 */

require_once __DIR__.'/../Libraries/autoload.php';

use GO\Scheduler;
define('TMP_DIR', '/tmp');
// Create a new scheduler
$scheduler = new Scheduler();

// Run process.php script at every minute
$scheduler->php(__DIR__ . '/process.php')
    ->onlyOne(TMP_DIR)
    ->everyMinute()
    ->output(__DIR__ . '/../logs/debug.log', true);
// Run delete_uploaded_files.php once daily
$scheduler->php(__DIR__ . '/delete_uploaded_files.php')
    ->onlyOne(TMP_DIR)
    ->daily()
    ->output(__DIR__ . '/../logs/debug.log', true);

// Let the scheduler execute jobs which are due.
$scheduler->run();
