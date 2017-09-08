<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/2/17
 * Time: 9:49 AM
 */

namespace AWorDS\App\Models;

/**
 * Class Directories
 *
 *
 * Directory Hierarchies & fixed files:
 * 1. Before the project is completed
 * Config::WORKING_DIRECTORY/Projects/{project_id}/Files/
 *                                                      ./generated/
 *                                                      ./original/
 *                                                      ./config.json
 *
 * 2. After the completion
 * Config::PROJECT_DIRECTORY/{project_id}/
 *                                       ./Files/
 *                                              ./generated/
 *                                              ./original/
 *                                       ./config.json
 *                                       ./DistanceMatrix.txt
 *                                       ./Output.txt
 *                                       ./SpeciesRelation.json
 *                                       ./SpeciesRelation.txt
 *                                       ./UPGMA tree.jpg
 *                                       ./Neighbour tree.jpg
 *
 * @package AWorDS\App\Models
 */
class Directories extends Model
{
    // store flags
    const STORE_COPY     = 1;
    const STORE_DOWNLOAD = 2;
    const STORE_MOVE     = 3;
    const STORE_STRING   = 4;

    /**
     * @var array
     */
    private $_directories;
    private $_pwd;
    private $_files = [];

    /**
     * Directories constructor.
     * @param int $project_id
     */
    function __construct($project_id){
        parent::__construct();

        $this->_directories = [
            'root' => self::WORKING_DIRECTORY . "/Projects/{$project_id}/Files",
            'generated' => null,
            'original'  => null,
            'project'   => self::WORKING_DIRECTORY . "/Projects/{$project_id}"
        ];
        $this->_directories['generated'] = $this->_directories['root'] . '/generated';
        $this->_directories['original']  = $this->_directories['root'] . '/original';

        $this->_pwd = $this->_directories['root'];
    }

    /**
     * Create necessary project directories
     */
    function create(){
        // Delete it if `Files` has already been found
        if(file_exists($this->root())) passthru('rm -rf ' . $this->root());
        mkdir($this->generated(), 0777, true);
        mkdir($this->original(), 0777, true);
    }

    /**
     * Load all the files from the present working directory
     */
    function loadFiles(){
        // Flash previous files
        $this->_files = [];
        $files = array_diff(scandir($this->pwd()), array('..', '.'));
        foreach ($files as $file){
            $this->_files[$file] = $this->pwd() . '/' . $file;
        }
    }

    /**
     * Get the directory of the filename
     * @param string $filename
     * @return bool|string
     */
    function get($filename){
        $this->loadFiles();
        foreach ($this->_files as $file_name => $file){
            if($filename === $file_name) return $file;
        }
        return false;
    }

    function getAll(){
        $this->loadFiles();
        return array_values($this->_files);
    }

    /**
     * Get the root directory of the project
     * @return string
     */
    function root(){
        return $this->_directories['root'];
    }

    /**
     * Get the generated directory of the project
     * @return string
     */
    function generated(){
        return $this->_directories['generated'];
    }

    /**
     * Get the original directory of the project
     * @return string
     */
    function original(){
        return $this->_directories['original'];
    }

    function project_dir(){
        return $this->_directories['project'];
    }

    /**
     * Change present working directory
     *
     * @param string $dir
     * @param bool   $force Whether to force cd to the directory (use with caution)
     * @return bool
     */
    function cd($dir, $force = false){
        if($force && is_dir($dir)){
            $this->_pwd = $dir;
            return true;
        }

        foreach ($this->_directories as $type => $directory){
            if($dir === $directory){
                $this->_pwd = $directory;
                return true;
            }
        }
        return false;
    }

    /**
     * Get present working directory
     *
     * @return string
     */
    function pwd(){
        return $this->_pwd;
    }

    /**
     * Stores data to the target directories
     *
     * NOTE: it overwrites the current file
     *
     * @param string $filename Target filename with extension (NOT a file path)
     * @param string $source A filename, url, string
     * @param int $flag STORE_COPY, STORE_MOVE, STORE_STRING
     * @return bool
     */
    function store($filename, $source, $flag){
        $filename = $this->pwd() . '/' . $filename;
        switch ($flag){
            case self::STORE_STRING:
                return file_put_contents($filename, $source) === false ? false : true;
            case self::STORE_COPY:
            case self::STORE_DOWNLOAD:
                return copy($source, $filename);
            case self::STORE_MOVE:
                return rename($source, $filename);
            default:
                return false;
        }
    }
}