<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/20/17
 * Time: 5:22 PM
 */

namespace ADACT\App\Models;


use ADACT\Config;

class FileUploader extends Model{
    /**
     * File upload related constants
     */
    const SIZE_LIMIT_EXCEEDED = 1;
    const INVALID_MIME_TYPE   = 2;
    const INVALID_FILE        = 3;
    const FILE_UPLOAD_FAILED  = 4;
    const FILE_UPLOAD_SUCCESS = 0;

    const HEADER_PREFIX = '>';

    private $_zip_file = null;

    function __construct(){
        parent::__construct();
    }

    /**
     * @param array $zip_file
     * @return array|int
     */
    function upload($zip_file){
        $this->_zip_file = $zip_file;
        if($zip_file['error'] !== 0) return self::FILE_UPLOAD_FAILED;
        // 1. Size limit
        if($zip_file['size'] > Config::MAX_UPLOAD_SIZE){
            unlink($zip_file['tmp_name']);
            return self::SIZE_LIMIT_EXCEEDED;
        }
        // 2. MIME: not always check-able: skip
        if(!($zip_file['type'] == 'application/zip' || $zip_file['type'] == 'application/octet-stream')) return self::INVALID_MIME_TYPE;
        // 3. See if it can be moved
        $tmp_dir = Config::WORKING_DIRECTORY . '/' . (time() + mt_rand());
        mkdir($tmp_dir, 0777, true);
        $tmp_file = $tmp_dir . '/' . basename($zip_file['name']);
        if(!move_uploaded_file($zip_file['tmp_name'], $tmp_file)){
            unlink($zip_file['tmp_name']);
            return self::INVALID_FILE;
        }
        // 4. Is it a valid zip?
        $zip_archive = new \ZipArchive();
        if($zip_archive->open($tmp_file, \ZipArchive::CHECKCONS) !== true) {
            exec("rm -Rf {$tmp_dir}");
            return self::INVALID_FILE;
        }
        // Extract file to $tmp_dir
        $zip_archive->extractTo($tmp_dir);
        $zip_archive->close();
        // Delete the $tmp_file
        unlink($tmp_file);
        // 5. Is everything in order?
        $files = $this->dir_list($tmp_dir, true);
        // 5.1 Each file size limit check + extract FASTA from multi FASTA
        $data = [];
        foreach($files as $file){
            $data = array_merge($data, $this->extract_FASTA($file, $tmp_dir));
            foreach ($data as $datum){
                $_file = $tmp_dir . '/' . $datum['id'] . '.fasta';
                // If a single file size exceeds the MAX_FILE_SIZE, show error
                if(filesize($_file) > Config::MAX_FILE_SIZE){
                    exec("rm -Rf {$tmp_dir}");
                    return self::SIZE_LIMIT_EXCEEDED;
                }
            }
        }

        // Everything's in order
        // Generate sha512 value
        $id = hash('sha512', $tmp_dir);
        $this->_store($tmp_dir, $id);
        // Return success
        return ['data' => $data, 'id' => $id];
    }

    function getFromID($id){
        if(@$stmt = $this->mysqli->prepare('SELECT directory FROM uploaded_files WHERE sha512_value = ?')){
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0){
                $stmt->bind_result($dir);
                $stmt->fetch();
                return $dir;
            }
        }
        return false;
    }

    function removeByID($id){
        if(@$stmt = $this->mysqli->prepare('DELETE FROM uploaded_files WHERE sha512_value = ?')){
            $stmt->bind_param('s', $id);
            if($stmt->execute()) return true;
        }
        return false;
    }

    /**
     * Extracts single FASTA from multiple FASTA
     *
     * Note: The output file is located at $target/$id.fasta
     *
     * @param string $filename The FASTA file containing multiple items
     * @param string $target   Target directory
     * @return array associative array [header, id]
     */
    private function extract_FASTA($filename, $target){
        $source_fp = fopen($filename, 'r');
        $data = [];
        $count = 0;
        while(!feof($source_fp)){
            $line = fgets($source_fp);
            if(substr($line, 0, 1) === self::HEADER_PREFIX){ // header is found
                $header = trim(substr($line, 1, strlen($line)-1));
                do{
                    $id = time() + ($count++);
                    $file_name = $target . '/' . $id . ".fasta";
                }while(file_exists($file_name));

                $target_fp = fopen($file_name, 'w');
                $info = ["header" => $header, "id" => $id];
                array_push($data, $info);
            }
            if(isset($target_fp)) fwrite($target_fp, trim($line) . "\n");
        }
        unlink($filename);
        return $data;
    }

    /**
     * dir_list method
     *
     * @param string $directory Any directory
     * @param bool   $full      Whether to include full directory (takes more time)
     * @return array List of directories or empty array if not a directory
     */
    private function dir_list($directory, $full = true){
        // Remove `.` and `..` from the list
        $files = is_dir($directory) ? array_diff(scandir($directory), array('..', '.')) : [];
        // Generate full path filename if $full is true
        if($full){
            foreach($files as &$file) $file = $directory . '/' . $file;
        }
        return $files;
    }

    private function _store($tmp_dir, $sha512_value){
        if(@$stmt = $this->mysqli->prepare('INSERT INTO uploaded_files(sha512_value, directory) VALUE (?, ?)')){
            $stmt->bind_param('ss', $sha512_value, $tmp_dir);
            if($stmt->execute()) return true;
        }
        return false;
    }
}