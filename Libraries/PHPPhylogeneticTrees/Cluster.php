<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/28/17
 * Time: 10:24 PM
 */

namespace PHPPhylogeneticTrees;

/**
 * Class Cluster
 *
 * The abstract class of clusters or rooted trees
 *
 * @package PHPPhylogeneticTrees
 */
abstract class Cluster{
    /** @var int Cluster identifier */
    protected $lab;
    /** @var int|array Advanced cluster identifier */
    protected $label;
    /** @var int The number of sequences in the cluster */
    protected $card;
    /** @var Cluster|null Left and right children, or null */
    protected $left,  $right;
    /** @var double[] Distances to lower-numbered nodes, or null */
    protected $d_matrix = [];

    public function alive() { return $this->d_matrix !== null; }

    public function kill() { $this->d_matrix = null; }

    public function getValue($index){ return $this->d_matrix[$index]; }

    public function getLabel(){ return $this->label; }

    public function getCard(){ return $this->card; }
}