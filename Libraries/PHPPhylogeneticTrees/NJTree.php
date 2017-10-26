<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/28/17
 * Time: 10:34 PM
 */

namespace PHPPhylogeneticTrees;

/**
 * Class NJTree
 *
 * The neighbour-joining algorithm.  Make a rooted tree by arbitrarily
 * adding a root node with edges to the last two leaves
 *
 * @package PHPPhylogeneticTrees
 */
class NJTree extends Tree{
    /** @var int The initial number of leaves */
    public $leaves_count;
    /** @var double[] The average distance to other leaves */
    public $avg_distance = []; // Should be 2 * leaves_count - 1

    function __construct($ds) {
        $this->leaves_count = count($ds);
        for ($i = 0; $i < $this->leaves_count; $i++){
            $this->cluster[$i] = new NJCluster($i + 1, $ds[$i]);
        }
        $this->cluster_count = $this->leaves_count;
        $highest_cluster_count = 2 * $this->leaves_count - 2;
        while ($this->cluster_count < $highest_cluster_count){
            $this->findAndJoin();
        }

        // Two leaves remain; cluster[K-1] is one of them, go find the other
        // Arbitrarily add a root node at this point
        $second_cluster_count = $this->cluster_count - 2;
        while (!$this->cluster[$second_cluster_count]->alive()) --$second_cluster_count;

        $dij = $this->value($second_cluster_count, $this->cluster_count - 1) / 2;
        // print ("Joining " . $this->K . "[$dij] and " . ($second_cluster_count + 1) . "[$dij] to form " . ($this->K + 1));
        $this->cluster[$this->cluster_count] = new NJCluster([$this->cluster[$this->cluster_count - 1]->getLabel(), $this->cluster[$second_cluster_count]->getLabel()], null, $this->cluster[$second_cluster_count], $dij, $this->cluster[$this->cluster_count - 1], $dij);
        $this->cluster_count++;
    }

    /**
     * The average distance to other leaves
     */
    private function computeAvgDistance() {
        for ($i = 0; $i < $this->cluster_count; ++$i){
            if ($this->cluster[$i]->alive()) {
                /** @var double Sum of the rest of the leaves */
                $sum = 0.0;
                for ($k = 0; $k < $this->cluster_count; ++$k)
                    if ($this->cluster[$k]->alive() && $k != $i){
                        $sum += $this->value($i, $k);
                    }
                /** @var int The current number of leaves */
                $L = 2 * $this->leaves_count - $this->cluster_count;
                $this->avg_distance[$i] = $sum / ($L - 2);              // Strange, but the book says so (p 171)
            }
        }
    }

    protected function findAndJoin() { // Find closest two live clusters and join them
        $this->computeAvgDistance();
        $x = -1; $y = -1;
        $min_value = INF;
        for ($i = 0; $i < $this->cluster_count; $i++)
            if ($this->cluster[$i]->alive())
                for ($j = 0; $j < $i; $j++)
                    if ($this->cluster[$j]->alive()) {
                        $cell_value = $this->value($i, $j) - ($this->avg_distance[$i] + $this->avg_distance[$j]);
                        if ($cell_value < $min_value) {
                            $min_value = $cell_value;
                            $x = $i;
                            $y = $j;
                        }
                    }
        $this->join($x, $y);
    }

    /**
     * Join x and y to form a new node (cluster)
     *
     * @param int $x
     * @param int $y
     */
    protected function join($x, $y) { // Join i and j to form node K
        /** @var double[] $d_mat Distance Matrix data */
        $d_mat = []; // Should be cluster_count
        $dij = $this->value($x, $y);
        for ($m = 0; $m < $this->cluster_count; ++$m)
            if ($this->cluster[$m]->alive() && $m != $x && $m != $y)
                $d_mat[$m] = ($this->value($x, $m) + $this->value($y, $m) - $dij) / 2;
        $dik = ($dij + $this->avg_distance[$x] - $this->avg_distance[$y]) / 2;
        $djk = $dij - $dik;
        $this->cluster[$this->cluster_count] = new NJCluster([$this->cluster[$x]->getLabel(), $this->cluster[$y]->getLabel()], $d_mat, $this->cluster[$x], $dik, $this->cluster[$y], $djk);
        $this->cluster[$x]->kill();
        $this->cluster[$y]->kill();
        ++$this->cluster_count;
    }
}
