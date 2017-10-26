<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/28/17
 * Time: 10:39 PM
 */

namespace PHPPhylogeneticTrees;

/**
 * Class TreeGenerator
 *
 * Generates UPGMA and/or NJ Tree based on the provided distance matrix
 * and labels (species names).
 *
 * This function can be extended to provide a way to directly include
 * distance matrix and labels (species names)
 *
 * @package PHPPhylogeneticTrees
 */
abstract class TreeGenerator{
    const UPGMA = 'UPGMATree';
    const NJ    = 'NJTree';

    /** @var double[][] Distance matrix */
    protected $_matrix = [];
    /** @var string[] Species names (they must follow the same sequence as the distance matrix) */
    protected $_labels = [];
    /** @var NJTree|UPGMATree */
    protected $_tree;

    /**
     * Generate either UPGMA or NJ tree
     *
     * @param string $tree_type Tree::UPGMA or Tree::NJ
     * @return $this
     */
    public function generate_tree($tree_type){
        $tree_class    = "PHPPhylogeneticTrees\\$tree_type";
        $this->_labels = $this->set_labels();
        $this->_matrix = $this->set_matrix();
        $this->_tree   = new $tree_class($this->_matrix);
        return $this;
    }

    /**
     * Get raw a hierarchical structure containing only some numbers
     *
     * @return array|int
     */
    public function getLabels(){
        return $this->_tree->getLabels();
    }

    /**
     * Get raw a hierarchical structure containing the provided labels
     *
     * @return mixed
     */
    function getFormattedLabels(){
        $labels = $this->_tree->getLabels();
        $this->format_labels($labels);
        return $labels;
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
    abstract protected function set_matrix();

    /**
     * This function is used to set labels or Species names
     *
     * The labels must be in the same order as the distance matrix
     *
     * @return mixed[]
     */
    abstract protected function set_labels();


    /**
     * Replace the default numeric labels with the given labels
     *
     * NOTE: Overwrite this function to get
     *
     * @param array $labels
     */
    protected function format_labels(&$labels){
        if(is_array($labels)){
            foreach ($labels as &$label){
                if(is_array($label)){
                    $this->format_labels($label);
                }else{
                    $label = $this->_labels[$label-1];
                }
            }
        }
    }
}
