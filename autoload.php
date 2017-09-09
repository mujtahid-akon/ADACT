<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/18/2017
 * Time: 1:42 AM
 */

if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    print 'This application requires PHP version 5.6 or higher.';
    exit();
}

/**
 * Register the autoloader for classes.
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
