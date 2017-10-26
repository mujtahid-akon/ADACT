<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/25/17
 * Time: 1:16 PM
 */

namespace AWorDS\App\Models;

require_once __DIR__ . "/../../Libraries/PHPPhylogeneticTrees/autoload.php";

use PHPPhylogeneticTrees\TreeGenerator;

class Tree extends TreeGenerator{
    /** @var FileManager */
    protected $_fm;
    /** @var ProjectConfig */
    protected $_config;

    function __construct($project_id){
        $this->_fm = new FileManager($project_id);
        $this->_config = new ProjectConfig($this->_fm->get(FileManager::CONFIG_JSON));
        $this->_fm->cd($this->_fm->root());
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
     *
     * @return double[][]
     */
    protected function set_matrix()
    {
        $matrix = [];
        $dm = fopen($this->_fm->get(FileManager::DISTANT_MATRIX), "r");
        $c_labels = count($this->_labels);
        for ($i = 0; $i < $c_labels; ++$i){
            for ($j = 0, $temp = []; $j < $i; ++$j){
                array_push($temp, (float)trim(fgets($dm)));
            }
            array_push($matrix, $temp);
        }
        return $matrix;
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
     * This formatting is used for Treant.js
     * @param mixed[] $labels
     */
    protected function format_labels(&$labels){
        if(is_array($labels)){
            foreach ($labels as &$label){
                if(is_array($label)){
                    $this->format_labels($label);
                    $label = ["children" => $label];
                }else{
                    $label = ["text" => ["name" => $this->_labels[$label-1]]];
                }
            }
        }
    }
}
