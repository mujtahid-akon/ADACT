<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/19/2017
 * Time: 9:15 PM
 */

namespace AWorDS\App\Controllers;

use AWorDS\App\Views\Template;
use AWorDS\App\Route;
use AWorDS\Config;

class Controller
{
    protected $_model;
    protected $_controller;
    protected $_action;
    protected $_method;
    protected $_params;
    protected $_url_params;
    protected $_template;
    
    /**
     * Output types (GUI, Redirect, JSON)
     */
    protected $_redirect = false;
    protected $_GUI      = true;
    protected $_JSON     = false;
    
    protected $_redirect_location = Config::WEB_DIRECTORY;
    protected $_JSON_contents     = [];
    protected $_GUI_load_view     = true;
    
    

    function __construct($controller, $action, $method, $params, $url_params) {
        $this->_controller  = $controller;
        $this->_action      = $action;
        $this->_method      = $method;
        $this->_url_params  = $url_params;
//      $parameters = [];
//      $parameters_o = [];
        
        if(in_array($method, [Route::GET, Route::POST])){
            if($method == Route::GET){
                $parameters = $_GET;
                $input_method = INPUT_GET;
            }else{
                $parameters = $_POST;
                $input_method = INPUT_POST;
            }
            foreach($params as $param => &$value){
                $filter_type = $value;
                switch($filter_type){
                    case Route::BOOLEAN:
                        $value = isset($parameters[$param]) ? (preg_match(Route::BOOLEAN, $parameters[$param]) ? true : false) : false; break;
                    case Route::EMAIL:
                    case Route::FLOAT:
                    case Route::INTEGER:
                    case Route::STRING:
                    case Route::HTML:
                        $value = isset($parameters[$param]) ? filter_input($input_method, $param, $filter_type) : null; break;
                    default:
                        // FIXME: needed to be implemented upon required
                }
                
//                 $parameters_o[$param] = (isset($parameters[$param])) ? $parameters[$param] : null;
            }
        }
        $this->_params= $params;
 
        if($this->_GUI AND $this->_GUI_load_view) $this->_template =& new Template($controller, $action);
    }
  
    function set_model($model = null){
        if($model == null) $model = $this->_controller;
        $model = '\\AWorDS\\App\\Models\\' . $model;
        $this->_model = $model;
        $this->$model =& new $model;
    }

    function set($name, $value){
        if($this->_GUI AND $this->_GUI_load_view) $this->_template->set($name, $value);
        elseif($this->_JSON){
            if($name == null){
                array_push($this->_JSON_contents, $value);
            }else{
                $this->_JSON_contents[$name] = $value;
            }
        }
    }

    function __destruct(){
        if($this->_redirect) header("Location: {$this->_redirect_location}");
        elseif($this->_JSON) print json_encode($this->_JSON_contents);
        elseif($this->_GUI AND $this->_GUI_load_view) $this->_template->render();
    }
}