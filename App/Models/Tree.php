<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/25/17
 * Time: 1:16 PM
 */

namespace AWorDS\App\Models;


abstract class Tree{
    protected $_x;
    protected $_y;
    protected $_matrix  = [];
    protected $_labels = [];
    protected $_fm;
    protected $_config;

    function __construct($project_id){
        //parent::__construct();
        $this->_fm = new FileManager($project_id);
        $this->_config = new ProjectConfig($this->_fm->get(FileManager::CONFIG_JSON));
        $this->_fm->cd($this->_fm->root());
    }

    function generate_tree(){
        $this->get_labels();
        $this->get_matrix();
        for($i = count($this->_labels); $i > 1; --$i){
            # Locate lowest cell in the table
            $this->lowest_cell();
            # Join the table on the cell co-ordinates
            $this->join_table();
            # Update the labels accordingly
            $this->join_labels();
        }
        # Return the final label
        return json_encode($this->_labels[0]);
    }

    protected function get_matrix(){
        $dm = fopen($this->_fm->get(FileManager::DISTANT_MATRIX), "r");
        $c_labels = count($this->_labels);
        for ($i = 0; $i < $c_labels; ++$i){
            for ($j = 0, $temp = []; $j < $i; ++$j){
                array_push($temp, (float)trim(fgets($dm)));
            }
            array_push($this->_matrix, $temp);
        }
    }

    protected function get_labels(){
        $data = $this->_config->data;
        foreach($data as $datum){
            array_push($this->_labels, $datum['short_name']);
        }
    }

    /**
     * Find the coordinate of the lowest value containing cell
     */
    protected function lowest_cell(){
        $min_cell = INF;
        foreach($this->_matrix as $row_index => $row){
            foreach($row as $col_index => $col){
                if((float)$col < $min_cell){
                    $min_cell = (float)$col;
                    $this->_x = $row_index;
                    $this->_y = $col_index;
                }
            }
        }
    }


    # join_table:
    #   Joins the entries of a table on the cell (a, b) by averaging their data entries
    protected function join_table(){
        $a = &$this->_x;
        $b = &$this->_y;
        $table = &$this->_matrix;
        # Swap if the indices are not ordered
        $c_table = count($table);

        if ($b < $a){
            $t = $a;
            $a = $b;
            $b = $t;
        }

        # For the lower index, reconstruct the entire row (A, i), where i < A
        $row = [];
        for($i = 0; $i < $a; ++$i){
            array_push($row, ($table[$a][$i] + $table[$b][$i])/2);
        }
        $table[$a] = $row;

        # Then, reconstruct the entire column (i, A), where i > A
        #   Note: Since the matrix is lower triangular, row b only contains values for indices < b
        for($i = $a+1; $i < $b; ++$i){
            $table[$i][$a] = ($table[$i][$a] + $table[$b][$i])/2;
        }

        #   We get the rest of the values from row i
        for($i = $b+1; $i < $c_table; ++$i){

            $table[$i][$a] = ($table[$i][$a]+$table[$i][$b])/2;
            unset($table[$i][$b]);
            $table[$i] = array_values($table[$i]);
        }
        unset($table[$b]);
        $table = array_values($table);
    }

    # join_labels:
    #   Combines two labels in a list of labels
    protected function join_labels(){
        $a = &$this->_x;
        $b = &$this->_y;
        $labels = &$this->_labels;
        # Swap if the indices are not ordered
        if ($b < $a){
            $t = $a;
            $a = $b;
            $b = $t;
        }

        # Join the labels in the first index
        $label_a = is_array($labels[$a]) ? ["children" => $labels[$a]] : ["text" => ["name" => $labels[$a]]];
        $label_b = is_array($labels[$b]) ? ["children" => $labels[$b]] : ["text" => ["name" => $labels[$b]]];
        $labels[$a] = [$label_a, $label_b];

        # Remove the (now redundant) label in the second index
        unset($labels[$b]);
        $labels = array_values($labels);
    }
}