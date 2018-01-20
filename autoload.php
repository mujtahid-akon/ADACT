<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/18/2017
 * Time: 1:42 AM
 */

// Version checking
if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    print 'This application requires PHP version 5.6 or higher.';
    exit();
}

// Platform checking
if (!in_array(PHP_OS, ['Linux', 'Darwin'])) {
    print 'This application can only be run on Linux or macOS';
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
    $file = str_replace('ADACT/', '', $file);
    $file = __DIR__ . '/' . $file . '.php';
    // if the file exists, require it once
    if (file_exists($file)) require_once $file;
});
