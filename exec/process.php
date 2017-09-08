<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/7/17
 * Time: 5:04 PM
 */
session_start();

require_once __DIR__ . '/../autoload.php';

if($argc != 2){
    session_destroy();
    exit(1);
}

extract(json_decode($argv[1], true));
/**
 * Extracted from supplied argument
 *
 * @var int       $project_id
 * @var int       $user_id
 * @var array|int $uploaded_files
 */

(new \AWorDS\App\Models\Process($project_id, $user_id, $uploaded_files))->init();

session_destroy();
exit(0);
