<?php
/**
 * File: delete_uploaded_files.php
 *
 * Deletes all the uploaded files that are at least seven days old.
 *
 * Created by PhpStorm.
 * User: muntashir
 * Date: 12/9/17
 * Time: 10:00 PM
 */

use ADACT\App\Models\FileUploader;

require_once __DIR__ . '/../autoload.php';

$datetime = new DateTime();
$datetime->setTimestamp(strtotime('-3 days'));

$file = new FileUploader();
$no_files = $file->deleteUploaded($datetime);

// DEBUG output
print "[ ".date('Y-m-d H:i:s')." ] ";
if($no_files === false){
    print "Deleting uploaded files failed!";
}else{
    print "Deleted {$no_files} uploaded files from {$datetime->format('Y-m-d H:i:s')}";
}
print "\n";
