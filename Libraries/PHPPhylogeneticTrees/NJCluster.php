<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/28/17
 * Time: 10:26 PM
 */

namespace PHPPhylogeneticTrees;
/**
 * Class NJCluster
 *
 * Neighbour clusters or trees, built by the neighbour joining algorithm
 *
 * @package PHPPhylogeneticTrees
 */
class NJCluster extends Cluster {
    /** @var int|null Length of edges to the children, if any */
    protected $d_left, $d_right;

    function __construct($lab, $d_mat, NJCluster $left = null, $dleft = null, NJCluster $right = null, $dright = null) { // Leaves = single sequences // $dmat = []
        $this->lab    = is_array($lab) ? array_sum($lab) : $lab;
        $this->label  = $lab;
        $this->d_matrix = $d_mat;
        $this->left   = $left;
        $this->d_left = $dleft;
        $this->right  = $right;
        $this->d_right= $dright;
        $this->card   = ($left === null) ? 1 : $left->card + $right->card;
    }
}
