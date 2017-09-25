<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/24/17
 * Time: 9:43 PM
 */

namespace AWorDS\App\Models;


class UPGMATree extends Tree{
    /**
     * Find the coordinate of the lowest value containing cell
     */
    protected function lowest_cell(){
        $min_cell = INF;
        foreach($this->_matrix as $row_index => $row){
            foreach($row as $col_index => $col){
                if($col < $min_cell){
                    $min_cell = $col;
                    $this->_x = $row_index;
                    $this->_y = $col_index;
                }
            }
        }
    }
}
