<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/25/17
 * Time: 1:21 PM
 */

namespace AWorDS\App\Models;


class NeighborJoiningTree extends Tree{
    protected function lowest_cell(){
        # Set default to infinity
        $min_cell = INF;
        $x = -1;
        $y = -1;
        $table = &$this->_matrix;
        $c_table = count($table);

        $indegree = [];
        for($i=0; $i<$c_table; ++$i) array_push($indegree, 0);

        for($i = 0; $i < $c_table; ++$i){
            for($j = 0; $j < $c_table; ++$j){
                if ($i == $j) continue;
                elseif ($i > $j) $indegree[$i] = $indegree[$i] + $table[$i][$j];
                elseif ($i < $j) $indegree[$i] = $indegree[$i] + $table[$j][$i];
            }
        }

        # Go through every cell, looking for the lowest
        foreach($table as $row_index => $row){
            foreach($row as $col_index => $col){
                $min_value = ($c_table - 2) * $col - $indegree[$row_index] - $indegree[$col_index];
                if($min_value < $min_cell){
                    $min_cell = $min_value;
                    $this->_x = $row_index;
                    $this->_y = $col_index;
                }
            }
        }
    }
}