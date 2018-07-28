<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/2/17
 * Time: 9:49 AM
 */

namespace ADACT\App\Models;

/**
 * Class FileManager
 *
 *
 * Directory Hierarchies & fixed files:
 * 1. New Project (before completion): PENDING_PROJECT
 *  ```
 * Config::WORKING_DIRECTORY/Projects/{project_id}/Files/
 *                                                      ./generated/
 *                                                      ./original/
 *                                                      ./config.json
 *  ```
 * 2. New Project (after completion), also the last project (editable): NEW_PROJECT|LAST_PROJECT
 *  ```
 * Config::PROJECT_DIRECTORY/{project_id}/
 *                                      ./Files/
 *                                            ./generated/
 *                                            ./original/
 *                                      ./config.json
 *                                      ./DistanceMatrix.txt
 *                                      ./Output.txt
 *                                      ./SpeciesRelation.json
 *                                      ./SpeciesRelation.txt
 *                                      ./UPGMA tree.png
 *                                      ./Neighbour tree.png
 *  ```
 * 3. Other projects: REGULAR_PROJECT
 *  ```
 * Config::PROJECT_DIRECTORY/{project_id}/
 *                                      ./config.json
 *                                      ./DistanceMatrix.txt
 *                                      ./Output.txt
 *                                      ./SpeciesRelation.json
 *                                      ./SpeciesRelation.txt
 *                                      ./UPGMA tree.png
 *                                      ./Neighbour tree.png
 *  ```
 *
 * NOTE: FileManager has only one dependency, which is the project ID
 *
 * @package ADACT\App\Models
 */
class FileManager extends Model{
    /* File constants */
    const SPECIES_RELATION         = 'SpeciesRelation.txt';
    const SPECIES_RELATION_JSON    = 'SpeciesRelation.json';
    const DISTANCE_MATRIX          = 'DistanceMatrix.txt';
    const DISTANT_MATRIX_FORMATTED = 'Output.txt';
    const NEIGHBOUR_TREE           = 'Neighbour tree.newick.txt';
    const UPGMA_TREE               = 'UPGMA tree.newick.txt';
    const CONFIG_JSON              = 'config.json';
    const DEBUG_LOG                = 'debug.log';

    /* Store flags */
    /** Copy file/URL to the pwd */
    const STORE_COPY     = 1;
    /** Same as FileManager::STORE_COPY */
    const STORE_DOWNLOAD = 2;
    /** Move files to the pwd */
    const STORE_MOVE     = 3;
    /** Store string as file in the pwd */
    const STORE_STRING   = 4;

    /** @var array Stores the base directories of the current project */
    private $_directories;
    /** @var string Present working directory */
    private $_pwd;
    /** @var array Stores the files under the pwd upon loadFiles method is called */
    private $_files = [];
    /** @var int Project type (Project::NEW_PROJECT|Project::LAST_PROJECT|Project::PENDING_PROJECT|Project::REGULAR_PROJECT) */
    private $_project_type;
    /** @var int Current project ID */
    private $_project_id;
    /** @var int|null Edit mode (PendingProjects::PROJECT_INIT_FROM_INIT|PendingProjects::PROJECT_INIT_FROM_AW|PendingProjects::PROJECT_INIT_FROM_DM) */
    private $_edit_mode;
    /** @var int Result type */
    private $_result_type;

    /**
     * Directories constructor.
     *
     * NOTE: Providing project type explicitly will simply simulate the project
     *  directories as if the project has the given project type. So, if the real
     *  project type is different from the given one, chances are that at least some
     *  of the directories do not exist. So, if any modification to the directory is
     *  necessary, let the system decide the project type (by not setting the project
     *  type explicitly). It has also other consequences.
     *
     * @param int      $project_id   Current project ID
     * @param null|int $project_type Project type (Project::NEW_PROJECT|Project::LAST_PROJECT|Project::PENDING_PROJECT|Project::REGULAR_PROJECT)
     */
    function __construct($project_id, $project_type = null){
        parent::__construct();
        $this->_project_id   = $project_id;
        $project = new Project($project_id);
        $this->_project_type = $project_type === null ? $project->getProjectType() : $project_type;
        $this->_edit_mode    = $project_type !== null ? null : $project->getEditMode();
        $this->_result_type  = $project_type !== null ? null : $project->getResultType();
        $this->_set_directories();
    }

    /**
     * Create necessary project directories
     * @return $this
     */
    function create(){
        // Delete it if `Files` has already been found
        if(file_exists($this->project_dir())) passthru('rm -rf ' . $this->root());
        mkdir($this->generated(), 0777, true);
        mkdir($this->original(), 0777, true);
        return $this;
    }

    /**
     * Load all the files from the present working directory
     * @return $this
     * @throws FileException Directory doesn't exist
     */
    function loadFiles(){
        // Flash previous files
        $this->_files = [];
        // Throw exception if pwd doesn't exist
        if(!file_exists($this->_pwd)) throw new FileException("The directory “{$this->_pwd}” doesn't exist!", FileException::E_DIRECTORY_DOES_NOT_EXIST);
        // Scan the pwd
        $files = array_diff(scandir($this->_pwd), array('..', '.'));
        // Add directory at the end
        foreach ($files as $file){
            $this->_files[$file] = $this->_pwd . '/' . $file;
        }
        return $this;
    }

    /**
     * Get the directory of the filename
     * @param string $filename
     * @param bool $force Create file if not exists
     * @return string Filename with directory
     * @throws FileException File doesn't exist
     */
    function get($filename, $force = false){
        if($force){
            $filename = $this->_pwd . '/' . $filename;
            if(!file_exists($filename)) touch($filename);
            return $filename;
        }

        $this->loadFiles();
        foreach ($this->_files as $file_name => $file){
            if($filename === $file_name) return $file;
        }
        // File isn't found, so throw an exception
        throw new FileException("The requested file “{$filename}” is not found at “{$this->_pwd}”", FileException::E_FILE_DOES_NOT_EXIST);
    }

    /**
     * Get all the file names
     * @return array
     */
    function getAll(){
        $this->loadFiles();
        return array_values($this->_files);
    }

    /**
     * Get the generated project type
     *
     * @return int
     */
    function getProjectType(){
        return $this->_project_type;
    }

    /**
     * Get edit mode
     *
     * NOTE: edit mode is always set to null when project type is provided explicitly.
     * @return int|null
     */
    function getEditMode(){
        return $this->_edit_mode;
    }

    function getResultType(){
        return $this->_result_type;
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
     * @return null|string null if the project type is Project::REGULAR_PROJECT, otherwise directory string
     */
    function generated(){
        return $this->_directories['generated'];
    }

    /**
     * Get the original directory of the project
     * @return null|string null if the project type is Project::REGULAR_PROJECT, otherwise directory string
     */
    function original(){
        return $this->_directories['original'];
    }

    /**
     * Get the project directory
     * @return null|string null if the project type is Project::REGULAR_PROJECT, otherwise directory string
     */
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
     * Stores data to the present working directory
     *
     * NOTE: it overwrites the current file
     *
     * @param string $filename Target filename with extension (NOT a file path)
     * @param string $source A filename, url, string
     * @param int $flag self::STORE_COPY|self::STORE_MOVE|self::STORE_STRING
     * @return bool
     * @throws FileException Invalid Store Flag
     */
    function store($filename, $source, $flag){
        $filename = $this->_pwd . '/' . $filename;
        switch ($flag){
            case self::STORE_STRING:
                return file_put_contents($filename, $source) === false ? false : true;
            case self::STORE_COPY:
            case self::STORE_DOWNLOAD:
                return copy($source, $filename);
            case self::STORE_MOVE:
                return rename($source, $filename);
            default:
                throw new FileException("Invalid store flag!", FileException::E_INVALID_STORE_FLAG);
        }
    }

    /**
     * Deletes the root folder with all contents.
     * Use with cautions.
     *
     * @return bool
     */
    function self_destruct(){
        return $this->rmdir($this->root(), true);
    }

    /**
     * Removes directories
     *
     * @param string $dir The directory to delete
     * @param bool $recursive Whether to delete files recursively
     * @return bool
     */
    private function rmdir($dir, $recursive = false){
        // CD to the requested directory
        if($this->cd($dir, true)){
            if(!$recursive) return rmdir($this->_pwd);
            else{
                $this->loadFiles();
                $pwd = $this->_pwd;
                foreach ($this->_files as $file){
                    if(is_dir($file)) $this->rmdir($file, true);
                    else unlink($file);
                }
                return rmdir($pwd);
            }
        }
        return false;
    }

    /**
     * Set directories during the initialization according to the
     * edit mode and project type.
     */
    private function _set_directories(){
        $working_dir = ($this->_result_type === Project::RT_PENDING AND $this->_edit_mode === Project::EM_INIT_FROM_INIT) ?
            self::WORKING_DIRECTORY . '/Projects' : self::PROJECT_DIRECTORY;
        $this->_directories = [
            'root' => $working_dir . "/{$this->_project_id}",
            'generated' => null,
            'original'  => null,
            'project'   => null
        ];
        if($this->_project_type != Project::PT_REGULAR){
            $this->_directories['project']   = $this->_directories['root'] . "/Files";
            $this->_directories['generated'] = $this->_directories['project'] . '/generated';
            $this->_directories['original']  = $this->_directories['project'] . '/original';
        }
        $this->_pwd = ($this->_result_type === Project::RT_PENDING AND $this->_edit_mode === PendingProjects::EM_INIT_FROM_INIT) ? $this->_directories['project'] : $this->_directories['root'];
    }
}