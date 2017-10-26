<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 10/25/17
 * Time: 4:46 PM
 */

namespace AWorDS\App\Controllers\API;


use AWorDS\App\Controllers\Controller;
use AWorDS\App\HttpStatusCode;

class API extends Controller{
    private $_status = [
        'code' => null,
        'message' => null
    ];
    function __construct($controller, $action, $method, array $params, array $url_params){
        parent::__construct($controller, $action, $method, $params, $url_params);
        $this->json();
    }

    function handle_default(){
        $this->status(HttpStatusCode::BAD_REQUEST, "Invalid request.");
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