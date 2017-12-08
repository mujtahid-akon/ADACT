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

// Create a new scheduler
$scheduler = new Scheduler();

// Run process.php script at every minute
$scheduler->php(__DIR__ . '/process.php')
    ->onlyOne(Config::WORKING_DIRECTORY)
    ->output(__DIR__ . '/../logs/process.log', true);

// Let the scheduler execute jobs which are due.
$scheduler->run();
