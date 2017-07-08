<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/18/2017
 * Time: 1:42 AM
 */

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    throw new Exception('AWorDS requires PHP version 5.4 or higher.');
}

/**
 * Register the autoloader for AWorDS classes.
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class)
{

    $file = str_replace('\\', '/', $class);
    $file = str_replace('AWorDS/', '', $file);
    $file = __DIR__ . '/' . $file . '.php';

    // if the file exists, require it once
    if (file_exists($file)) require_once $file;
});