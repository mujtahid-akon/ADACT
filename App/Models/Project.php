<?php

namespace ADACT\App\Models;

use ADACT\App\HttpStatusCode;
use \ADACT\Config;
use ADACT\App\Models\FileManager as FM;

class Project extends ProjectPrivilegeHandler {
    /* Input types */
    const INPUT_TYPE_FILE     = 'file';
    const INPUT_TYPE_ACCN_GIN = 'accn_gin';

    /* Project related constants */
    const PROJECT_DELETE_SUCCESS = 0;
    const PROJECT_DELETE_FAILED  = 1;
    const PROJECT_DOES_NOT_EXIST = 2;

    /* Export project related constants */
    const EXPORT_SPECIES_RELATION = 1;
    const EXPORT_DISTANCE_MATRIX  = 2;
    const EXPORT_NEIGHBOUR_TREE   = 3;
    const EXPORT_UPGMA_TREE       = 4;
    const EXPORT_ALL              = 0;

    /** Number of config.json allowed per JSON request */
    const MAX_CONFIG_ALLOWED      = 5;

    /** @var null|int Current user ID */
    private $_user_id;

    /** @var ProjectConfig */
    protected $config;

    function __construct($project_id = null){
        parent::__construct($project_id);
        $this->_user_id = isset($_SESSION) ? $_SESSION['user_id'] : null;
    }

    /**
     * Creates a new project
     *
     * @param array $config
     * @return null|int returns the id number on success, null on failure
     * @throws FileException
     */
    function add($config){
        $this->config = new ProjectConfig();
        $this->config->load_config($config);
        // Check if the config file is in order
        if(!$this->config->verify()) return null;
        // If so, create a new project
        $project_id = null;
        // Save project info in the DB, and get inserted id (which is the project id)
        if(@$stmt = $this->mysqli->prepare('INSERT INTO `projects`(`user_id`, `project_name`, `date_created`) VALUE(?, ?, NOW())')){
            $stmt->bind_param('is', $this->_user_id, $this->config->project_name);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1){
                // Set the inset_id as the project id
                $project_id = $stmt->insert_id;
                // Add to pending list
                (new PendingProjects())->add($project_id);
                // Set this project as the last project
                (new LastProjects())->set($project_id);
                // Create necessary directories
                $dir = new FM($project_id);
                $dir->create();
                // Store config.json to the root directory
                $dir->store(FM::CONFIG_JSON, json_encode($config, JSON_PRETTY_PRINT), $dir::STORE_STRING);
                // Success
                return $project_id;
            }
        }
        return null;
    }

    /**
     * Creates multiple projects
     * Currently a user can request to create up to 5 projects at a time
     *
     * @param string $config
     * @return array
     * @throws FileException
     */
    function addMultiple($config){
        $project_info = [];
        $config = json_decode(htmlspecialchars_decode($config), true);
        if(!$this->is_array($config)) throw new FileException("The json file is not an array", FileException::E_FILE_FORMAT_ERROR);
        $i = 0;
        foreach ($config as $conf){
            ++$i;
            try{
                $project_id = $this->add($conf);
                if($project_id == null) throw new FileException("Malformed config.json", FileException::E_FILE_FORMAT_ERROR);
                $project = [
                    'name' => isset($conf['project_name']) ? $conf['project_name'] : "#project",
                    'id' => $project_id
                ];
                array_push($project_info, $project);
            } catch (FileException $e) {}
            if($i == self::MAX_CONFIG_ALLOWED) break;
        }
        return $project_info;
    }

    /**
     * Edit project.
     * This function has provided enough functionality to make it
     * to the process. It determines where to begin the processing
     * when editing a process.
     *
     * If Absent Word type is changed, execution begins from self::EM_INIT_FROM_AW.
     * Otherwise, execution begins from self::EM_INIT_FROM_DM.
     *
     * @param array $config
     * @param int $project_id
     * @return bool
     * @throws FileException
     */
    function edit($config, $project_id){
        $this->_project_id = $project_id;
        if($this->can_edit()){
            $base_cf = new ProjectConfig((new FM($this->_project_id))->get(FM::CONFIG_JSON));
            $part_cf = (new ProjectConfig())->load_config($config);
            // Is the config is modified at all?
            $modification_level = self::EM_INIT_FROM_INIT; // 0 = No, 1 = AW, 2 = DM
            // Check for changes in KMer
            if(property_exists($part_cf, 'kmer')
                AND ((isset($part_cf->kmer['max']) AND $base_cf->kmer['max'] !== $part_cf->kmer['max'])
                    OR (isset($part_cf->kmer['max']) AND $base_cf->kmer['min'] !== $part_cf->kmer['min']))){
                $modification_level = self::EM_INIT_FROM_DM;
                $kmer = [
                    'min' => $part_cf->kmer['min'],
                    'max' => $part_cf->kmer['max']
                ];
                $base_cf->setConfig('kmer', $kmer);
            }
            // Check for change in inversion
            if(property_exists($part_cf, 'inversion')
                AND $base_cf->inversion !== $part_cf->inversion){
                $modification_level = self::EM_INIT_FROM_DM;
                $base_cf->setConfig('inversion', $part_cf->inversion);
            }
            // Check for change in dissimilarity_index
            if(property_exists($part_cf, 'dissimilarity_index')
                AND $base_cf->dissimilarity_index !== $part_cf->dissimilarity_index){
                $modification_level = self::EM_INIT_FROM_DM;
                $base_cf->setConfig('dissimilarity_index', $part_cf->dissimilarity_index);
            }
            // Check for change in aw_type
            if(property_exists($part_cf, 'aw_type')
                AND $base_cf->aw_type !== $part_cf->aw_type){
                $modification_level = self::EM_INIT_FROM_AW;
                $base_cf->setConfig('aw_type', $part_cf->aw_type);
            }
            if($modification_level === self::EM_INIT_FROM_INIT) return HttpStatusCode::NOT_MODIFIED;
            // OK, save the modified config
            // and add to pending list with one of the edit modes
            if(!((new PendingProjects())->add($this->_project_id, $modification_level) AND $base_cf->save())) return HttpStatusCode::INTERNAL_SERVER_ERROR;
            (new Notifications())->set_unseen($this->_project_id);
            return 0; // Success
        }
        return HttpStatusCode::BAD_REQUEST;
    }

    /**
     * getAll method.
     *
     * Returns all the project info associated with the respective user
     *
     * @param bool $formatted Whether to return formatted info instead of raw info
     * @return array containing 'id', 'name', 'date_created', 'editable', 'last' and 'result_type' or just empty array on failure
     */
    function getAll($formatted = false){
        $projects = [];
        // Get projects info in descending order
        if(@$stmt = $this->mysqli->prepare('SELECT p.project_id, p.project_name, CONVERT_TZ(p.date_created, \'SYSTEM\', \'UTC\') AS date_created, count(last.project_id) AS editable, pen.cancel AS cancel, pen.status_code AS status_code FROM projects AS p LEFT OUTER JOIN pending_projects AS pen ON p.project_id = pen.project_id LEFT OUTER JOIN last_projects AS last ON p.project_id = last.project_id WHERE p.user_id = ? GROUP BY p.project_id ORDER BY p.date_created DESC;')){
            $stmt->bind_param('i', $this->_user_id);
            $stmt->execute();
            $stmt->store_result();
            for($i = 0; $i < $stmt->num_rows; $i++){
                // Push the project to the array
                array_push($projects, $this->_fetch_project_overview($stmt, $formatted));
            }
        }
        return $projects;
    }

    /**
     * getMany method.
     *
     * Get project info for only the requested IDs
     *
     * @param array $project_ids
     * @param bool $formatted Whether to return formatted info instead of raw info
     * @return array
     */
    function getMultiple($project_ids, $formatted = false){
        $projects = [];
        // parameterize the project IDs for the sql
        foreach ($project_ids as &$project_id){ // Add parenthesis around the IDs
            $project_id = '(' . $project_id . ')';
        }
        $project_ids = implode(',', $project_ids); // Make them comma separated
        if($stmt = $this->mysqli->prepare('SELECT p.project_id, p.project_name, CONVERT_TZ(p.date_created, \'SYSTEM\', \'UTC\') AS date_created, count(last.project_id) AS editable, pen.cancel AS cancel, pen.status_code AS status_code FROM projects AS p LEFT OUTER JOIN pending_projects AS pen ON p.project_id = pen.project_id LEFT OUTER JOIN last_projects AS last ON p.project_id = last.project_id WHERE p.user_id = ? AND p.project_id IN (' . $project_ids . ') GROUP BY p.project_id ORDER BY p.date_created DESC;')){
            $stmt->bind_param('i', $this->_user_id);
            $stmt->execute();
            $stmt->store_result();
            for($i = 0; $i < $stmt->num_rows; $i++){
                // Push the project to the array
                array_push($projects, $this->_fetch_project_overview($stmt, $formatted));
            }
        }
        return $projects;
    }

    /**
     * get method.
     *
     * Get project info for the given project ID
     *
     * @param int $project_id
     * @param bool $formatted Whether to return formatted info instead of raw info
     * @return array containing 'id', 'name', 'date_created' and 'editable' or empty array on failure
     */
    function get($project_id, $formatted = false){
        // Get projects info in descending order
        if(@$stmt = $this->mysqli->prepare('SELECT p.project_id, p.project_name, CONVERT_TZ(p.date_created, \'SYSTEM\', \'UTC\') AS date_created, count(last.project_id) AS editable, pen.cancel AS cancel, pen.status_code AS status_code FROM projects AS p LEFT OUTER JOIN pending_projects AS pen ON p.project_id = pen.project_id LEFT OUTER JOIN last_projects AS last ON p.project_id = last.project_id WHERE p.user_id = ? AND p.project_id = '.$project_id)){
            $stmt->bind_param('i', $this->_user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows > 0){
                return $this->_fetch_project_overview($stmt, $formatted);
            }
        }
        return [];
    }

    /**
     * getAllPending method.
     *
     * Returns all the pending projects associated with the respective user
     *
     * @return array containing 'id', 'name', 'date_created' or just empty array on failure
     */
    function getAllPending(){
        $projects = [];
        // Get projects info in descending order
        if(@$stmt = $this->mysqli->prepare('SELECT a.project_id, a.project_name, CONVERT_TZ(a.date_created,\'SYSTEM\',\'UTC\'), b.status_code FROM projects AS a INNER JOIN pending_projects AS b ON a.project_id = b.project_id WHERE a.user_id = ? AND cancel = FALSE;')){
            $stmt->bind_param('i', $this->_user_id);
            $stmt->execute();
            $stmt->store_result();
            for($i = 0; $i < $stmt->num_rows; $i++){
                $project = ['id' => null, 'name' => null, 'date_created' => null];
                $stmt->bind_result($project['id'], $project['name'], $project['date_created'], $status_code);
                $stmt->fetch();
                // Push the project to the array only if the project is pending
                if($this->getResultType($status_code, false) === self::RT_PENDING) array_push($projects, $project);
            }
        }
        return $projects;
    }

    /**
     * Get details of a project
     * @param $project_id
     * @return array
     * @throws FileException
     */
    function getDetails($project_id){
        $this->_project_id = $project_id;
        $rt = $this->getResultType();
        $fm = new FM($this->_project_id);
        if($rt === self::RT_SUCCESS){
            // Get Species Relations
            $species_relations = json_decode(file_get_contents($fm->get(FM::SPECIES_RELATION_JSON)), true);
            // Get Species names
            $species = $this->_get_species_from_species_relation($species_relations);
            $tree    = new Tree($this->_project_id, Tree::GENERAL);
            $dm      = $this->_get_distance_matrix($species, $fm);
            array_unshift($dm[0], null);
            $upgma_tree = $tree->generate_tree($tree::UPGMA)->getFormattedLabels();
            $nj_tree    = $tree->generate_tree($tree::NJ)->getFormattedLabels();
        }else{
            $dm         = null;
            $species_relations = null;
            $upgma_tree = null;
            $nj_tree    = null;
        }

        return [
            'config'    => ($rt === self::RT_CANCELLED) ? null : (new ProjectConfig($fm->get(FM::CONFIG_JSON)))->getConfigAssocArray(),
            'meta_data' => [
                'success'   => ($rt === self::RT_SUCCESS),
                'editable'  => $this->isEditable(),
                'pending'   => ($rt === self::RT_PENDING),
                'cancelled' => ($rt === self::RT_CANCELLED),
                'failed'    => ($rt === self::RT_FAILED)
            ],
            'result'    => ($rt === self::RT_SUCCESS) ? [
                'distance_matrix' => $dm,
                'sorted_species_relations' => $species_relations,
                'UPGMA_tree' => $upgma_tree,
                'NJ_tree'    => $nj_tree
            ] : null
        ];
    }

    /**
     * export method
     *
     * Exports SpeciesRelation, DistanceMatrix, UPGMA Tree, Neighbour Tree and a zipped file
     * based on the user request
     *
     * @param int $project_id The project ID of which content is needed to be shown
     * @param int $type Which type of file is requested
     * @return null|array     array containing mime, name, path on success, null on failure
     * @throws FileException
     */
    function export($project_id, $type){
        $file = null;
        // Verify project id
        if(!$this->verify($project_id)) return $file;
        // Set project info
        $fm = new FM($project_id);
        switch($type){
            case self::EXPORT_SPECIES_RELATION:
                $file['mime'] = 'text/plain';
                $file['name'] = FM::SPECIES_RELATION;
                $file['path'] = $fm->get(FM::SPECIES_RELATION);
                break;
            case self::EXPORT_DISTANCE_MATRIX:
                $file['mime'] = 'text/plain';
                $file['name'] = FM::DISTANCE_MATRIX;
                $file['path'] = $fm->get(FM::DISTANT_MATRIX_FORMATTED);
                break;
            case self::EXPORT_NEIGHBOUR_TREE:
                $file['mime'] = 'image/png';
                $file['name'] = FM::NEIGHBOUR_TREE;
                $file['path'] = $fm->get(FM::NEIGHBOUR_TREE);
                break;
            case self::EXPORT_UPGMA_TREE:
                $file['mime'] = 'image/png';
                $file['name'] = FM::UPGMA_TREE;
                $file['path'] = $fm->get(FM::UPGMA_TREE);
                break;
            case self::EXPORT_ALL: // FIXME: Use cron job delete the zip file
                $file['mime'] = 'application/zip';
                $file['name'] = 'project_' . $project_id . '.zip'; // e.g. project_29
                $file['path'] = Config::WORKING_DIRECTORY . '/' . $file['name'];
                // Create zip
                $zip = new \ZipArchive();
                if ($zip->open($file['path'], \ZipArchive::CREATE)!==TRUE) {
                    return null;
                }
                // Add files to zip
                $zip->addFile($fm->get(FM::SPECIES_RELATION), '/' . FM::SPECIES_RELATION);
                $zip->addFile($fm->get(FM::DISTANT_MATRIX_FORMATTED), '/'. FM::DISTANCE_MATRIX);
                $zip->addFile($fm->get(FM::NEIGHBOUR_TREE), '/' . FM::NEIGHBOUR_TREE);
                $zip->addFile($fm->get(FM::UPGMA_TREE), '/' . FM::UPGMA_TREE);
                $zip->addFile($fm->get(FM::CONFIG_JSON), '/' . FM::CONFIG_JSON);
                $zip->close();
        }
        // set $file to null if the file isn't found
        if(!$file['path']) $file = null;
        return $file;
    }

    /**
     * @param int[] $project_ids
     * @return null|string
     * @throws FileException
     */
    function exportAll($project_ids){
        $files = [];
        $file_path = Config::WORKING_DIRECTORY . '/projects_'. time() . mt_rand(100, 999) . '.zip';
        foreach ($project_ids as $project_id){
            $file = $this->export($project_id, self::EXPORT_ALL);
            if($file !== null) array_push($files, $file);
        }
        // Create zip
        $zip = new \ZipArchive();
        if ($zip->open($file_path, \ZipArchive::CREATE)!==TRUE) {
            return null;
        }
        foreach ($files as $file){
            $zip->addFile($file['path'], '/' . basename($file['path']));
        }
        $zip->close();

        foreach ($files as $file) unlink($file['path']);
        if(!file_exists($file_path)) return null;
        return $file_path;
    }

    /**
     * Deletes a project permanently. The process includes,
     * - Deleting from the last_project table
     * - Deleting from the pending_projects table
     * - Deleting from the projects table
     * - Delete the respective project files
     *
     * @param int      $project_id The project ID which will be deleted
     * @param int|null $user_id    Current user ID
     * @return int Project::PROJECT_DELETE_SUCCESS|Project::PROJECT_DOES_NOT_EXIST|Project::PROJECT_DELETE_FAILED
     */
    function delete($project_id, $user_id = null){
        $this->_project_id = $project_id;
        if($user_id !== null) $this->_user_id = $user_id;
        $fm = new FM($this->_project_id);
        // If the project to be deleted is the last project of the user,
        // delete it from that table too.
        (new LastProjects())->removeByID($project_id);
        // Delete as pending project
        (new PendingProjects($this->_project_id, $this->_user_id))->remove();
        // Delete project from the database
        if(@$stmt = $this->mysqli->prepare('DELETE FROM projects WHERE user_id = ? AND project_id = ?')){
            $stmt->bind_param('ii', $this->_user_id, $this->_project_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1){
                // Delete the project files
                if($fm->self_destruct()) return self::PROJECT_DELETE_SUCCESS;
            }else return self::PROJECT_DOES_NOT_EXIST;
        }
        return self::PROJECT_DELETE_FAILED;
    }

    /**
     * verify method
     *
     * Verifies a project against the current user
     *
     * @param int $project_id
     * @return bool
     */
    function verify($project_id){
        if(@$stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM projects WHERE user_id = ? AND project_id = ?')){
            $stmt->bind_param('ii', $this->_user_id, $project_id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            if($count == 1) return true;
        }
        return false;
    }

    /**
     * @param int $project_id
     * @return bool
     * @throws FileException
     */
    function can_fork($project_id){
        if(!$this->verify($project_id)) return false;
        if((new PendingProjects($project_id))->isA()) return false;
        if((new ProjectConfig((new FM($project_id))->get(FM::CONFIG_JSON)))->type !== 'accn_gin') return false;
        return true;
    }

    public function can_edit($project_id = null){
        if($this->_project_id == null) $this->_project_id = $project_id;
        return !(new PendingProjects($this->_project_id))->isA() AND (new LastProjects())->isA($this->_project_id);
    }

    /* Private functions */

    /**
     * Helper for get() and getAll()
     * @param \mysqli_stmt $stmt
     * @param bool         $formatted Return formatted data instead of raw data (for API)
     * @return array
     */
    private function _fetch_project_overview(&$stmt, $formatted){
        $project = ['id' => null, 'name' => null, 'date_created' => null, 'editable' => false, 'last' => false, 'result_type' => self::RT_SUCCESS];
        $stmt->bind_result($project['id'], $project['name'], $project['date_created'], $project['last'], $cancel, $status_code);
        $stmt->fetch();
        $cancel = $cancel === 1 ? true : false;
        $project['result_type'] = ($status_code === null OR $cancel === null) ? self::RT_SUCCESS : $this->getResultType($status_code, $cancel);
        $project['last'] = $project['last'] === 1 ? true : false;
        // A project is editable if it is the last project, the project was successful and the ‘Files’ directory exists
        $project['editable'] = ($project['last'] AND $project['result_type'] === self::RT_SUCCESS AND file_exists(Config::PROJECT_DIRECTORY . '/' . $project['id'] . '/Files')) ? true : false;
        if($formatted){ // Formatted output [id, name, date_created, editable, success, pending, failed, cancelled]
            unset($project['last']);
            $project['success']   = $project['result_type'] === self::RT_SUCCESS;
            $project['pending']   = $project['result_type'] === self::RT_PENDING;
            $project['failed']    = $project['result_type'] === self::RT_FAILED;
            $project['cancelled'] = $project['result_type'] === self::RT_CANCELLED;
            unset($project['result_type']);
        }
        return $project;
    }

    private function _get_species_from_species_relation($species_relations){
        $species_list = [];
        foreach ($species_relations as $species => $relation){
            array_push($species_list, $species);
        }
        return $species_list;
    }

    /**
     * Get distance matrix HTML table
     *
     * @param array $species
     * @param FM $fm
     * @return array Each member is a table row
     * @throws FileException
     */
    private function _get_distance_matrix($species, FM $fm){
        $total_species = count($species); // Number of rows and columns is the same as this + 1 for header
        $distance_matrix = file($fm->get(FM::DISTANCE_MATRIX));
        $dm_i = 0; // Distance matrix pointer
        $table_rows = [];
        array_push($table_rows, $species);
        for($row_i  = 0; $row_i < $total_species; ++$row_i){
            $table_row = [];
            // Header first
            array_push($table_row, $species[$row_i]);
            // Blank columns
            for($col_i = 0; $col_i <= $row_i; ++$col_i) array_push($table_row, null);
            // Now, the rest
            for(/* $col_i has already been set above */; $col_i < $total_species; ++$col_i){
                array_push($table_row, round($distance_matrix[$dm_i++], 4));
            }
            // add the row
            array_push($table_rows, $table_row);
        }
        return $table_rows;
    }

    /**
     * Whether the given data is a dictionary or an array
     * @param array $arr
     * @return bool
     */
    function is_array(array $arr) {
        if (array() === $arr)
            return true;
        ksort($arr);
        return !(array_keys($arr) !== range(0, count($arr) - 1));
    }
}
