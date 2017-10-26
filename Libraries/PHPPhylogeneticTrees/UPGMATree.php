<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/28/17
 * Time: 10:36 PM
 */

namespace PHPPhylogeneticTrees;

/**
 * Class UPGMATree
 *
 * The UPGMA algorithm
 *
 * @package PHPPhylogeneticTrees
 */
class UPGMATree extends Tree{
    public function __construct($ds) {
        $N = count($ds);
        for ($i = 0; $i < $N; ++$i)
            $this->cluster[$i] = new UPGMACluster($i + 1, $ds[$i]);
        $this->cluster_count = $N;
        while ($this->cluster_count < 2 * $N - 1)
            $this->findAndJoin();
    }

    protected function findAndJoin() { // Find closest two live clusters and join them
        $x = -1; $y = -1;
        $min_value = INF;
        for ($i = 0; $i < $this->cluster_count; ++$i)
            if ($this->cluster[$i]->alive())
                for ($j = 0; $j < $i; ++$j)
                    if ($this->cluster[$j]->alive()) {
                        $cell_value = $this->value($i, $j);
                        if ($cell_value < $min_value) {
                            $min_value = $cell_value;
                            $x = $i;
                            $y = $j;
                        }
                    }
        $this->join($x, $y);
    }

    protected function join($x, $y) { // Join i and j to form node K
        $dmat = []; // K
        for ($m = 0; $m < $this->cluster_count; ++$m)
            if ($this->cluster[$m]->alive() && $m != $x && $m != $y)
                $dmat[$m] = ($this->value($x, $m) * $this->cluster[$x]->getCard() + $this->value($y, $m) * $this->cluster[$y]->getCard()) / ($this->cluster[$x]->getCard() + $this->cluster[$y]->getCard());
        $this->cluster[$this->cluster_count] = new UPGMACluster([$this->cluster[$x]->getLabel(), $this->cluster[$y]->getLabel()], $dmat, $this->cluster[$x], $this->cluster[$y]);
        $this->cluster[$x]->kill();
        $this->cluster[$y]->kill();
        ++$this->cluster_count;
    }
}
