<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/28/17
 * Time: 10:34 PM
 */

namespace PHPPhylogeneticTrees;

/**
 * Class Tree
 * @package PHPPhylogeneticTrees
 */
abstract class Tree{
    /** @var Cluster[] The nodes (clusters) of the resulting tree */
    protected $cluster = [];
    /** @var int The number of clusters created so far */
    protected $cluster_count;

    public function getRoot() {
        return $this->cluster[$this->cluster_count - 1];
    }

    public function getLabels() {
        return $this->cluster[$this->cluster_count - 1]->getLabel();
    }

    /**
     * Get the distance matrix element for max x and min y
     * @param int $x
     * @param int $y
     * @return double|null
     */
    protected function value($x, $y) {
        return $this->cluster[max($x, $y)]->getValue(min($x, $y));
    }

    abstract protected function findAndJoin();
    abstract protected function join($x, $y);
}
