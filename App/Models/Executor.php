<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 1/22/18
 * Time: 10:01 PM
 */

namespace ADACT\App\Models;
use ADACT\Config;

/**
 * Class Executor
 *
 * Executes a single command.
 *
 * Executes commands using exec() but in a more formatted way than exec() does.
 *
 * NOTE: Don't use this class for running multiple commands at once
 *  ie. pipe can be used but not & or &&
 *
 * @package ADACT\App\Models
 */
class Executor{
    /** @var string Preserves log */
    private $_output;
    /** @var array Stores command as array */
    private $_command;
    /** @var Logger */
    private $_logger;
    /** @var mixed Return value */
    private $_return;
    private $_filename = null;

    /**
     * Executor constructor.
     * @param array|string $command
     * @param Logger|null  $logger
     */
    public function __construct($command, Logger &$logger = null){
        $this->_command = [];
        $this->_logger  = $logger;
        $this->_output  = "";
        $this->_return  = null;
        if(is_array($command)){
            $this->_command = array_merge($this->_command, $command);
        }else{
            array_push($this->_command, $command);
        }
    }

    /**
     * Same as the constructor
     * @param array|string $command
     * @param Logger|null $logger
     * @return $this
     */
    public function new($command, Logger &$logger = null){
        self::__construct($command, $logger);
        return $this;
    }

    /**
     * Adds new parameter(s) at the end of the current command list
     *
     * @param string|array $parameter
     * @return $this
     */
    public function add($parameter){
        if(is_array($parameter)){
            $this->_command = array_merge($this->_command, $parameter);
        }else{
            array_push($this->_command, $parameter);
        }
        return $this;
    }

    /**
     * Execute the command
     * @param bool $preserve_output
     * @return $this
     */
    public function execute($preserve_output = false){
        $this->_output = "";
        if($this->_logger !== null){
            $preserve_output = false;
            array_push($this->_command, $this->_logger->extract());
        }
        if($preserve_output){
            if($this->_filename === null) $this->_filename = $this->_getFilename();
            $logger = new Logger($this->_filename, false);
            array_push($this->_command, $logger->extract());
        }
        exec(implode(' ', $this->_command), $dummy_output, $this->_return);
//        print "Command: " . implode(' ', $this->_command) . "\n";
        if($preserve_output){
            $this->_output = file_get_contents($this->_filename);
            unlink($this->_filename);
        }
        array_pop($this->_command);
        return $this;
    }

    /**
     * Return the return value
     * @return mixed
     */
    public function returns(){
        return $this->_return;
    }

    /**
     * Return the output
     * @return string
     */
    public function output(){
        return $this->_output;
    }

    private function _getFilename(){
        $count = 0;
        do{
            $filename = Config::WORKING_DIRECTORY . '/' . time() . $count . '.log';
            ++$count;
        }while(file_exists($filename));
        return $filename;
    }

    public function __destruct(){
        if($this->_filename !== null) unlink($this->_filename);
    }
}