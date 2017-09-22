<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/2/17
 * Time: 9:49 AM
 */

namespace AWorDS\App\Models;

/**
 * Class FileManager
 *
 *
 * Directory Hierarchies & fixed files:
 * 1. New Project (before completion): PENDING_PROJECT
 * Config::WORKING_DIRECTORY/Projects/{project_id}/Files/
 *                                                      ./generated/
 *                                                      ./original/
 *                                                      ./config.json
 *
 * 2. New Project (after completion), also the last project (editable): NEW_PROJECT|LAST_PROJECT
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
 * 3. Other projects: REGULAR_PROJECT
 * Config::PROJECT_DIRECTORY/{project_id}/
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
class FileManager extends Model{
    // File constants
    const SPECIES_RELATION         = 'SpeciesRelation.txt';
    const SPECIES_RELATION_JSON    = 'SpeciesRelation.json';
    const DISTANT_MATRIX           = 'DistanceMatrix.txt';
    const DISTANT_MATRIX_FORMATTED = 'Output.txt';
    const NEIGHBOUR_TREE           = 'Neighbour tree.jpg';
    const UPGMA_TREE               = 'UPGMA tree.jpg';
    const CONFIG_JSON              = 'config.json';


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
    private $_project_type;
    private $_project_id;

    /**
     * Directories constructor.
     * @param int      $project_id
     * @param null|int $project_type
     */
    function __construct($project_id, $project_type = null){
        parent::__construct();
        $this->_project_id   = $project_id;
        $this->_project_type = ($project_type === null) ? (new Project($project_id))->getType() : $project_type;
        $this->_set_directories();
    }

    /**
     * Create necessary project directories
     */
    function create(){
        // Delete it if `Files` has already been found
        if(file_exists($this->project_dir())) passthru('rm -rf ' . $this->root());
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
     * @param string $source   A filename, url, string
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

    private function _set_directories(){
        $working_dir = ($this->_project_type === Project::PENDING_PROJECT) ?
            self::WORKING_DIRECTORY . '/Projects' : self::PROJECT_DIRECTORY;
        $this->_directories = [
            'root' => $working_dir . "/{$this->_project_id}",
            'generated' => null,
            'original'  => null,
            'project'   => $working_dir . "/{$this->_project_id}/Files"
        ];
        if($this->_project_type != Project::REGULAR_PROJECT){
            $this->_directories['project']   = $this->_directories['root'] . "/Files";
            $this->_directories['generated'] = $this->_directories['project'] . '/generated';
            $this->_directories['original']  = $this->_directories['project'] . '/original';

            $this->_pwd = $this->_directories['project'];
        }else{
            $this->_pwd = $this->_directories['root'];
        }
    }
}