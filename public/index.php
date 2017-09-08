<?php
session_start();

require_once __DIR__ . '/../autoload.php';

use \AWorDS\App\Route;
use \AWorDS\Config;

if(Config::DEBUG_MODE){
    error_reporting(E_ALL|E_DEPRECATED|E_ERROR|E_NOTICE);
    error_log("{$_SERVER['REQUEST_METHOD']}: {$_SERVER['REQUEST_URI']}");
}else{
    error_reporting(0);
}

// Set default timezone to UTC
// NOTICE: default timezone of MySQL won't be affected by this! (which is the server time)
date_default_timezone_set('UTC');

// If SSL is enabled but request is in HTTP, redirect to HTTPS
if(Config::USE_ONLY_SSL AND !((!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443'))){
    header("Location: https://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}");
    exit();
}

/*
 * Custom MVC Style designed particularly for this project
 */

/**
 * @var string $location Only letters, digits, underscores, dots, and hyphens are allowed
 */
$location = '/';
if(php_sapi_name() == 'cli-server'){
    $uri = explode('?', $_SERVER['REQUEST_URI']);
    $location = $uri[0];
}else{
    preg_match('/^(\/?[\w\.\-]+)+(?(?=&))/', $_SERVER['QUERY_STRING'], $matches); // (?(?=&))
    $location = '/' . (isset($matches[0]) ? $matches[0] : '');
}

/**
 * Add routes
 */
// Main page
Route::add(Route::GET, '/', 'Main@home');
Route::add(Route::GET, '/home', 'Main@home');
/* === User Login/logout/reg === */
Route::add(Route::POST, '/reg', 'User@register', ['name' => Route::STRING, 'email' => Route::EMAIL, 'pass' => Route::STRING]);
Route::add(Route::POST, '/login', 'User@login', ['email' => Route::EMAIL, 'pass' => Route::STRING, 'remember' => Route::BOOLEAN]);
Route::add(Route::POST, '/logout', 'User@logout', ['all' => Route::BOOLEAN]); // TODO: 'all' is not implemented
Route::add(Route::POST, '/reset_pass', 'User@reset_password', ['email' => Route::EMAIL, 'pass' => Route::STRING]);
Route::add(Route::GET, '/register_success', 'User@register_success');
Route::add(Route::GET, '/login', 'User@login_page', ['email' => Route::EMAIL]);
Route::add(Route::GET, '/unlock', 'User@unlock', ['email' => Route::EMAIL, 'key' => Route::STRING]);
Route::add(Route::GET, '/reset_pass', 'User@reset_password_page', ['email' => Route::EMAIL, 'key' => Route::STRING]);
Route::add(Route::GET, '/logout', 'User@logout');
/* === Project: Serial must be maintained! === */
Route::add(Route::GET, '/projects', 'Project@all_projects');
Route::add(Route::GET, '/projects/pending', 'Project@pending_projects'); // TODO
// New project
Route::add(Route::GET, '/projects/new', 'Project@new_project_page');
Route::add(Route::POST, '/projects/new', 'Project@new_project', ['config' => Route::STRING]);
Route::add(Route::POST, '/projects/file_upload', 'Project@file_upload');
// Regular project
Route::add(Route::GET, '/projects/last', 'Project@last_project');
Route::add(Route::GET, '/projects/{project_id}', 'Project@project_overview');
Route::add(Route::GET, '/projects/{project_id}/edit', 'Project@edit_project');  // TODO
Route::add(Route::GET, '/projects/{project_id}/process', 'Project@process_data');  // TODO
Route::add(Route::POST, '/projects/{project_id}/delete', 'Project@delete_project');
Route::add(Route::GET, '/projects/{project_id}/download', 'Project@download_project');
Route::add(Route::GET, '/projects/{project_id}/get/{file_name}', 'Project@get_file');

/**
 * Load views or do other specific tasks describe in the respective controller
 */
Route::load(Route::verify(strtoupper($_SERVER['REQUEST_METHOD']),$location));
exit;