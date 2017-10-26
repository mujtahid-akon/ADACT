<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/28/17
 * Time: 10:30 PM
 */

namespace PHPPhylogeneticTrees;

/**
 * Class UPGMACluster
 *
 * UPGMA clusters or trees, built by the UPGMA algorithm
 *
 * @package PHPPhylogeneticTrees
 */
class UPGMACluster extends Cluster{
    function __construct($lab, $d_mat, UPGMACluster $left = null, UPGMACluster $right = null) { // Leaves = single sequences // $dmat = []
        $this->lab    = is_array($lab) ? array_sum($lab) : $lab;
        $this->label  = $lab;
        $this->d_matrix = $d_mat;
        $this->left   = $left;
        $this->right  = $right;
        $this->card   = ($left === null) ? 1 : $left->card + $right->card;
    }
}
