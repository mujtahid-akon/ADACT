<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/28/17
 * Time: 11:31 PM
 */

/**
 * Generate phylogenetic trees by extending TreeGenerator class
 */

require_once __DIR__ . "/../autoload.php";

use PHPPhylogeneticTrees\TreeGenerator;

// Create new tree class by extending TreeGenerator class
class Tree extends TreeGenerator{

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
    protected function set_matrix(){
        return [
            [],
            [ 0.0427 ],
            [ 0.0858, 0.0441 ],
            [ 0.0702, 0.0358, 0.0282 ],
            [ 0.0088, 0.0476, 0.0910, 0.0751 ]
        ];
    }

    /**
     * This function is used to set labels or Species names
     *
     * The labels must be in the same order as the distance matrix
     *
     * @return mixed[]
     */
    protected function set_labels(){
        return ['dog', 'house_mouse', 'rat', 'human', 'cat'];
    }
}

// Create new tree
$tree = new Tree();

// Generate UPGMA tree
$tree->generate_tree($tree::UPGMA);
print "UPGMA (Raw): " . json_encode($tree->getLabels()) . "\n";
print "UPGMA (Formatted): " . json_encode($tree->getFormattedLabels()) . "\n";

// Generate NJ tree
$tree->generate_tree($tree::NJ);
print "NJ (Raw): " . json_encode($tree->getLabels()) . "\n";
print "NJ (Formatted): " . json_encode($tree->getFormattedLabels()) . "\n";

// Output
// UPGMA (Raw): [[[4,3],2],[5,1]]
// UPGMA (Formatted): [[["human","rat"],"house_mouse"],["cat","dog"]]
// NJ (Raw): [[[5,1],2],[4,3]]
// NJ (Formatted): [[["cat","dog"],"house_mouse"],["human","rat"]]
