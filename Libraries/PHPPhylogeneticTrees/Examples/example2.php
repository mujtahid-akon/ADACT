<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/28/17
 * Time: 11:57 PM
 */

/**
 * Generate phylogenetic trees w/o the given abstract class
 */

require_once __DIR__ . "/../autoload.php";

use PHPPhylogeneticTrees\UPGMATree;
use PHPPhylogeneticTrees\NJTree;

$d_mat = [
    [],
    [ 0.0427 ],
    [ 0.0858, 0.0441 ],
    [ 0.0702, 0.0358, 0.0282 ],
    [ 0.0088, 0.0476, 0.0910, 0.0751 ]
];

// Create UPGMA Tree
$upgma = new UPGMATree($d_mat);
print "UPGMA: " . json_encode($upgma->getLabels()) . "\n";

// Create NJ Tree
$nj = new NJTree($d_mat);
print "NJ: " . json_encode($nj->getLabels()) . "\n";

// Output
// UPGMA: [[[4,3],2],[5,1]]
// NJ: [[[5,1],2],[4,3]]
