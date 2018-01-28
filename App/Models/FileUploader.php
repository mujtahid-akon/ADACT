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
    /* File upload related constants */
    const SIZE_LIMIT_EXCEEDED = 1;
    const INVALID_MIME_TYPE   = 2;
    const INVALID_FILE        = 3;
    const FILE_UPLOAD_FAILED  = 4;
    const FILE_LIMIT_EXCEEDED = 5;
    const FILE_UPLOAD_SUCCESS = 0;

    /** Header prefix, usually ‘>’ */
    const HEADER_PREFIX = '>';

    /**
     * FileUploader constructor.
     */
    function __construct(){
        parent::__construct();
    }

    /**
     * upload method
     *
     * Uploader checks against a number of indices to check the validity of
     * the uploaded file. These indices include:
     * - File size limit (`Config::MAX_UPLOAD_SIZE`)
     * - Sequence size limit (`Config::MAX_FILE_SIZE`)
     * - Number of sequences allowed (`Config::MAX_FILE_ALLOWED`)
     * - Mime types (application/zip, application/octet-stream and text/plain)
     * - Validity of zip file
     *
     * @param array $up_file Uploaded file
     * @return array|int
     */
    function upload($up_file){
        if($up_file['error'] !== 0) return self::FILE_UPLOAD_FAILED;
        // 1. Size limit
        if($up_file['size'] > Config::MAX_UPLOAD_SIZE){
            unlink($up_file['tmp_name']);
            return self::SIZE_LIMIT_EXCEEDED;
        }
        // 2. MIME
        if(!($up_file['type'] == 'application/zip' || $up_file['type'] == 'application/octet-stream' || $up_file['type'] == 'text/plain')) return self::INVALID_MIME_TYPE;
        // 3. See if it can be moved
        $tmp_dir = Config::WORKING_DIRECTORY . '/' . (time() + mt_rand());
        mkdir($tmp_dir, 0777, true);
        $tmp_file = $tmp_dir . '/' . basename($up_file['name']);
        if(!move_uploaded_file($up_file['tmp_name'], $tmp_file)){
            unlink($up_file['tmp_name']);
            return self::INVALID_FILE;
        }
        // 4. Is it a valid zip?
        $zip_archive = new \ZipArchive();
        if($zip_archive->open($tmp_file, \ZipArchive::CHECKCONS) === true) {
            // Extract file to $tmp_dir
            $zip_archive->extractTo($tmp_dir);
            $zip_archive->close();
            // Delete the $tmp_file
            unlink($tmp_file);
        }
        // 5. Is everything in order?
        $files = $this->_dir_list($tmp_dir, true);
        // Each file size limit & quantity check + extract FASTA from multi FASTA.
        // Some checks are done multiple times intentionally in order to
        // increase execution time.
        $data = [];
        foreach($files as $file){
            $tmp_data = $this->_extract_FASTA($file, $tmp_dir);
            // 5.1 Max files allowed exceeded
            if(is_int($tmp_data)){
                exec("rm -Rf {$tmp_dir}");
                return self::FILE_LIMIT_EXCEEDED;
            }
            $data = array_merge($data, $tmp_data);
            // 5.2 Max files allowed exceeded, again
            if(count($data) > self::MAX_FILE_ALLOWED){
                exec("rm -Rf {$tmp_dir}");
                return self::FILE_LIMIT_EXCEEDED;
            }
            // The loop below can also be placed after the current foreach loop
            // but it's placed here intentionally to reduce execution time.
            // Replace $tmp_data with $data if placed after the current loop.
            foreach ($tmp_data as $datum){
                $_file = $tmp_dir . '/' . $datum['id'] . '.fasta';
                // 5.2 If a single file size exceeds the MAX_FILE_SIZE, show error
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

    /**
     * Fetch the directory of a particular uploaded file by its id, ie. sha512 value
     *
     * @param string $id
     * @return bool|string The name of directory on success or false on failure
     */
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

    /**
     * Remove uploaded file by its id, ie. sha512_value
     *
     * @param string $id sha512 value
     * @return bool
     */
    public function removeByID($id){
        if(@$stmt = $this->mysqli->prepare('DELETE FROM uploaded_files WHERE sha512_value = ?')){
            $stmt->bind_param('s', $id);
            if($stmt->execute()) return true;
        }
        return false;
    }

    /**
     * Delete upload files from server from a particular time.
     *
     * @param \DateTime $leastTime
     * @return int|false number of files that were deleted on success (returns 0 if no files) and False on failure
     */
    public function deleteUploaded(\DateTime $leastTime){
        $time = $leastTime->format('Y-m-d H:i:s');
        if($stmt = $this->mysqli->prepare('SELECT directory FROM uploaded_files WHERE date <= ?')){
            $stmt->bind_param('s', $time);
            $stmt->execute();
            $stmt->store_result();
            for($i = 0; $i < $stmt->num_rows; ++$i){
                $stmt->bind_result($directory);
                $stmt->fetch();
                if(file_exists($directory)) exec('rm -Rf "'.$directory.'"');
            }
        }
        if($stmt = $this->mysqli->prepare('DELETE FROM uploaded_files WHERE date <= ?')){
            $stmt->bind_param('s', $time);
            $stmt->execute();
            $stmt->store_result();
            if(!$stmt->errno) return $stmt->affected_rows;
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
     * @return int|array associative array containing [header, id] or self::FILE_LIMIT_EXCEEDED
     */
    private function _extract_FASTA($filename, $target){
        $source_fp = fopen($filename, 'r');
        $data = [];
        $count = 0;
        $sequence_count = 0;
        while(!feof($source_fp)){
            $line = fgets($source_fp);
            if(substr($line, 0, 1) === self::HEADER_PREFIX){ // header is found
                if(++$sequence_count > self::MAX_FILE_ALLOWED){
                    return self::FILE_LIMIT_EXCEEDED;
                }
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
    private function _dir_list($directory, $full = true){
        // Remove `.` and `..` from the list
        $files = is_dir($directory) ? array_diff(scandir($directory), array('..', '.')) : [];
        // Generate full path filename if $full is true
        if($full){
            foreach($files as &$file) $file = $directory . '/' . $file;
        }
        return $files;
    }

    /**
     * Stores uploaded file related info into the DB
     *
     * @param string $tmp_dir
     * @param string $sha512_value
     * @return bool
     */
    private function _store($tmp_dir, $sha512_value){
        if(@$stmt = $this->mysqli->prepare('INSERT INTO uploaded_files(sha512_value, directory, date) VALUE (?, ?, NOW())')){
            $stmt->bind_param('ss', $sha512_value, $tmp_dir);
            if($stmt->execute()) return true;
        }
        return false;
    }
}