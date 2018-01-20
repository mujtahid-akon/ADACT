<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/20/17
 * Time: 4:09 AM
 */

require_once __DIR__.'/../Libraries/autoload.php';
require_once __DIR__.'/../Config.php';

use GO\Scheduler;
use ADACT\Config;
//file_put_contents(__DIR__ . '/../logs/debug.log', date('Y-m-d H:i:s') . "\n", FILE_APPEND);
// Create a new scheduler
$scheduler = new Scheduler();

// Run process.php script at every minute
$scheduler->php(__DIR__ . '/process.php')
    ->onlyOne(Config::WORKING_DIRECTORY)
    ->everyMinute()
    ->output(__DIR__ . '/../logs/process.log', true);
// Run delete_uploaded_files.php once daily
$scheduler->php(__DIR__ . '/delete_uploaded_files.php')
    ->onlyOne(Config::WORKING_DIRECTORY)
    ->daily()
    ->output(__DIR__ . '/../logs/debug.log', true);

// Let the scheduler execute jobs which are due.
$scheduler->run();
