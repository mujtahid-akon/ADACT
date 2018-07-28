<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/25/17
 * Time: 1:16 PM
 */

namespace ADACT\App\Models;

require_once __DIR__ . "/../../Libraries/PHPPhylogeneticTrees/autoload.php";

use PHPPhylogeneticTrees\TreeGenerator;

class Tree extends TreeGenerator{
    const GENERAL   = 1;
    const TREANT_JS = 2;
    /** @var FileManager */
    protected $_fm;
    /** @var ProjectConfig */
    protected $_config;
    /** @var int For whom the tree is generated */
    private $_type;

    /**
     * Tree constructor.
     * @param $project_id
     * @param int $type
     * @throws FileException
     */
    function __construct($project_id, $type = self::GENERAL){
        $this->_type = $type;
        $this->_fm = new FileManager($project_id);
        $this->_config = new ProjectConfig($this->_fm->get(FileManager::CONFIG_JSON));
        $this->_fm->cd($this->_fm->getResultType() === Project::RT_SUCCESS ? $this->_fm->root() : $this->_fm->generated());
    }

    function getNewickFormat($tree_type){
        return str_replace('"', '',
                str_replace(']', ')',
                    str_replace('[', '(',
                        json_encode($this->generate_tree($tree_type)->getFormattedLabels()))))
            . ';';
    }

    /**
     * This function is used to set distance matrix
     *
     * Use this function to set $this->_matrix
     *
     * Matrix format:
     * <code>
     * [
     *  [ ],
     *  [ 0.0427 ],
     *  [ 0.0858, 0.0441 ],
     *  [ 0.0702, 0.0358, 0.0282 ],
     *  [ 0.0088, 0.0476, 0.0910, 0.0751 ]
     * ]
     * </code>
     * @return \double[][]
     * @throws FileException
     */
    protected function set_matrix(){
        $matrix = [];
        try{
            $file = $this->_fm->get(FileManager::DISTANCE_MATRIX);
            $dm = fopen($file, "r");
            $c_labels = count($this->_labels);
            for ($i = 0; $i < $c_labels; ++$i){
                for ($j = 0, $temp = []; $j < $i; ++$j){
                    array_push($temp, (float)trim(fgets($dm)));
                }
                array_push($matrix, $temp);
            }
            return $matrix;
        }catch (FileException $e){
            throw new FileException("Distance Matrix cannot be found at “{$this->_fm->pwd()}”", FileException::E_FILE_DOES_NOT_EXIST);
        }
    }

    /**
     * This function is used to set labels or Species names
     *
     * The labels must be in the same order as the distance matrix
     *
     * @return mixed[]
     */
    protected function set_labels(){
        $labels = [];
        $data = $this->_config->data;
        foreach($data as $datum){
            array_push($labels, $datum['short_name']);
        }
        return $labels;
    }

    /**
     * This formatting is used for Treant.js or general
     * @param mixed[] $labels
     */
    protected function format_labels(&$labels){
        if(is_array($labels)){
            foreach ($labels as &$label){
                if(is_array($label)){
                    $this->format_labels($label);
                    switch ($this->_type){
                        case self::TREANT_JS:
                            $label = ["children" => $label];
                            break;
                        case self::GENERAL:
                    }
                }else{
                    switch ($this->_type){
                        case self::TREANT_JS:
                            $label = ["text" => ["name" => $this->_labels[$label-1]]];
                            break;
                        case self::GENERAL:
                            $label = $this->_labels[$label-1];
                    }
                }
            }
        }
    }
}
