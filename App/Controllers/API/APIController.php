<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 10/25/17
 * Time: 4:46 PM
 */

namespace ADACT\App\Controllers\API;

use ADACT\App\Controllers\Controller;
use ADACT\App\HttpStatusCode;

/**
 * Class APIController
 *
 * Implementation of Restful API
 *
 * @see \ADACT\App\Controllers\Controller - Parent class of this class
 * @package ADACT\App\Controllers\API
 */
class APIController extends Controller{
    private $_status = [
        'code' => null,
        'message' => null
    ];
    function __construct($controller, $action, $method, array $params, array $url_params){
        parent::__construct($controller, $action, $method, $params, $url_params);
        $this->json();
    }

    function handle_default(){
        $this->status(HttpStatusCode::BAD_REQUEST, "Bad request.");
    }

    protected function status($code, $message){
        $this->response($code);
        $this->_status['code'] = $code;
        $this->_status['message'] = $message;
    }

    function __destruct(){
        $this->set('status', $this->_status);
        parent::__destruct();
    }
}