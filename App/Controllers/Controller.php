<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/19/2017
 * Time: 9:15 PM
 */

namespace ADACT\App\Controllers;

use ADACT\App\HttpStatusCode;
use ADACT\App\Models\Model;
use ADACT\App\Views\Template;
use ADACT\App\Route;
use ADACT\Config;

/*
 * Common Constants used in controllers
 */
define('ACTIVE_TAB', 'active_tab'); // Currently active tab
define('PAGE_TITLE', 'title');      // Page title
define('LOGGED_IN', 'logged_in');   // Is logged in
define('STANDALONE', 'standalone'); // Whether standalone html page is provided as view

/**
 * Class Controller
 *
 * Controller handles data in three ways:
 * - Send response as HTML (default)
 * - Send response as JSON data
 * - Send a redirection request
 *
 * These three functionality can be enhanced to
 * do almost anything.
 *
 * @see \ADACT\App\Controllers\API\APIController - Implementation of API controller using the Controller class
 * @package ADACT\App\Controllers
 */
class Controller{
    /**
     * @var int $response_code Any constant form HttpStatusCode
     */
    private $response_code = HttpStatusCode::OK;
    private $input_content = "";

    protected $_model;
    protected $_controller;
    protected $_action;
    protected $_method;
    protected $_params;
    protected $_url_params;
    protected $_template;

    /**
     * Output types (GUI, Redirect, JSON)
     *
     * @var bool $_redirect Redirect to the redirect location
     * @var bool $_HTML     Output to user
     * @var bool $_JSON     JSON output
     */
    protected $_redirect = false;
    protected $_HTML     = true;
    protected $_JSON     = false;

    protected $_redirect_location = Config::WEB_DIRECTORY;
    protected $_JSON_contents     = [];
    protected $_HTML_load_view    = true;

    /**
     * Controller constructor.
     * @param string $controller
     * @param string $action
     * @param string $method
     * @param array $params
     * @param array $url_params
     */
    function __construct($controller, $action, $method, $params, $url_params) {
        $this->_controller  = $controller;
        $this->_action      = $action;
        $this->_method      = $method;
        $this->_url_params  = $url_params;

        if(in_array($method, [Route::GET, Route::POST, Route::PUT, Route::DELETE, Route::HEAD, Route::PATCH, Route::OPTIONS])){
            switch ($method){
                case Route::GET:
                    $parameters = $_GET;
                    $input_method = INPUT_GET;
                    break;
                case Route::POST:
                    $parameters = $_POST;
                    $input_method = INPUT_POST;
                    break;
                default:
                    $parameters = $_REQUEST;
                    $input_method = INPUT_REQUEST;
            }

            foreach($params as $param => &$value){
                $filter_type = $value;
                switch($filter_type){
                    case Route::BOOLEAN:
                        $value = isset($parameters[$param]) ?
                            (preg_match(Route::BOOLEAN, $parameters[$param]) ? true : false) : false;
                        break;
                    case Route::EMAIL:
                    case Route::FLOAT:
                    case Route::INTEGER:
                    case Route::STRING:
                    case Route::HTML:
                        $value = isset($parameters[$param]) ? filter_input($input_method, $param, $filter_type) : null; break;
                    default:
                        // There isn't any default behaviour
                }
            }
        }
        $this->_params= $params;

        if(in_array($method, [Route::POST, Route::PUT, Route::PATCH])){
            $this->input_content = file_get_contents("php://input", false, stream_context_get_default(), 0, $_SERVER["CONTENT_LENGTH"]);
        }

        if($this->_HTML AND $this->_HTML_load_view) $this->_template = new Template($controller, $action);
    }

    /**
     * Get request parameters
     * @return array
     */
    function get_params(){
        return array_merge($this->_url_params, $this->_params);
    }

    /**
     * Get input contents from php://input
     * @return string
     */
    function get_contents(){
        return $this->input_content;
    }

    /**
     * Call a particular model class
     *
     * @param null|string $model
     * @return Model
     */
    function set_model($model = null){
        if($model == null) $model = $this->_controller;
        $model = '\\ADACT\\App\\Models\\' . $model;
        $this->_model = $model;
        $this->$model = new $model;
        return $this->$model;
    }

    /**
     * Set a value for a variable
     *
     * This variable is accessible in various ways depending on user actions
     * - For a redirection, it does nothing and isn't accessible
     * - For an HTML request, it is accessible in the Template class
     * - For a JSON request, it's not accessible but sent as part of JSON output
     *
     * @param null|string $name  variable name
     * @param mixed       $value value of the variable
     */
    function set($name, $value){
        if($this->_HTML AND $this->_HTML_load_view) $this->_template->set($name, $value);
        elseif($this->_JSON){
            if($name == null){
                array_push($this->_JSON_contents, $value);
            }else{
                $this->_JSON_contents[$name] = $value;
            }
        }
    }

    /**
     * Response code of the current request
     *
     * @var int $code Any constants from HttpStatusCode
     */
    function response($code){
        $this->response_code = $code;
    }

    /**
     * Redirect to a certain page
     *
     * @param string $location
     */
    function redirect($location = ''){
        $this->_redirect = true;
        $this->_redirect_location = $location;
        $this->_HTML = false;
        $this->_JSON = false;
    }

    /**
     * Whether to load view or not
     *
     * Only applies when _HTML = true
     *
     * @param bool $isItOk
     */
    function load_view($isItOk){
        $this->_HTML = true;
        $this->_redirect = false;
        $this->_JSON = false;
        $this->_HTML_load_view = $isItOk;
    }

    /**
     * Output as JSON instead of HTML
     *
     * @var array|null $content An optional array which is to be outputted (also can be set by $this->set)
     */
    function json($content = null){
        $this->_JSON = true;
        $this->_HTML = false;
        $this->_redirect = false;
        if(is_array($content)) $this->_JSON_contents = $content;
    }

    /**
     * Send response to the client on destruct
     */
    function __destruct(){
        try{
            $this->__send_response();
        } catch (\Exception $e) {
            error_log($e->getCode() . ": " . $e->getMessage());
            error_log(implode("\n", $e->getTrace()));
        }
    }

    /**
     * Send response to the client
     * @throws \Exception
     */
    private function __send_response(){
        http_response_code($this->response_code);
        if($this->_redirect) header("Location: ".Config::WEB_DIRECTORY."{$this->_redirect_location}");
        elseif($this->_JSON){
            header('Content-Type: application/json; charset=UTF-8');
            print json_encode($this->_JSON_contents, JSON_PRETTY_PRINT);
        }elseif($this->_HTML){
            if($this->_HTML_load_view) $this->_template->render();
        } /** @noinspection PhpStatementHasEmptyBodyInspection */ else{
            // Do nothing
        }
    }
}
