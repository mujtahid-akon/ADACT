<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/28/17
 * Time: 11:29 PM
 */


if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    print 'This application requires PHP version 5.4 or higher.';
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
    $file = str_replace('PHPPhylogeneticTrees/', '', $file);
    $file = __DIR__ . '/' . $file . '.php';
    // if the file exists, require it once
    if (file_exists($file)) require_once $file;
});
