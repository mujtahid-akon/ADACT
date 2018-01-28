<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 1/22/18
 * Time: 8:20 PM
 */

namespace ADACT\App\Models;

/**
 * Class Logger
 *
 * Logs to file
 *
 * @copyright Muntashir Al-Islam 2017
 * @author    Muntashir Al-Islam <muntashir.islam96@gmail.com>
 * @license   MIT License.
 * @package   ADACT\App\Models
 */
class Logger{
    const FORMAT = "\e[0m";
    const BOLD   = "\e[1m";
    const UNDERLINE = "\e[4m";
    const INVERT = "\e[7m";
    const WHITE  = "\e[30m";
    const RED    = "\e[31m";
    const YELLOW = "\e[32m";
    const ORANGE = "\e[33m";
    const INDIGO = "\e[34m";
    const VIOLET = "\e[35m";
    const GREEN  = "\e[36m";
    const GREY   = "\e[37m";
    const BG_WHITE = "\e[40m";
    const BG_RED   = "\e[41m";
    const BG_YELLOW = "\e[42m";
    const BG_ORANGE = "\e[43m";
    const BG_INDIGO = "\e[44m";
    const BG_VIOLET = "\e[45m";
    const BG_GREEN  = "\e[46m";
    const BG_GREY   = "\e[47m";

    private $_content;
    private $_file;
    private $_include_time;

    /**
     * Logger constructor.
     * @param string $filename
     * @param bool   $include_time Whether to include time
     */
    public function __construct($filename, $include_time = true){
        $this->_content = "";
        $this->_file = $filename;
        $this->_include_time = $include_time;
        if(!file_exists($this->_file)) touch($this->_file);
    }

    /**
     * Add message to the log
     * @param string $message
     * @param string $formats Use the constants joined using . (dot)
     * @return $this
     */
    public function log($message, $formats = null){
        if($formats !== null) $message = $formats . $message . self::FORMAT;
        $this->_content .= ($this->_include_time ? $this->_log_time() : '') . $message . "\n";
        return $this;
    }

    /**
     * Flush the logs to the log file
     * @return $this
     */
    public function flush(){
        if(file_exists($this->_file)) file_put_contents($this->_file, $this->_content, FILE_APPEND);
        $this->_content = "";
        return $this;
    }

    /**
     * Used for extracting stream from exec(), passthru() or system()
     *
     * @return string
     */
    public function extract(){
        if($this->_include_time) $this->_content .= $this->_log_time();
        $this->flush(); // First flush the stored logs
        return " >> \"{$this->_file}\" 2>&1";
    }

    /**
     * Logger destructor.
     */
    public function __destruct(){
        $this->flush();
    }

    private function _log_time(){
        return '['.date('D M d H:i:s Y').'] ';
    }
}