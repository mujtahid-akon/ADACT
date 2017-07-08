<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/18/2017
 * Time: 1:33 AM
 */

namespace AWorDS\App;

require_once __DIR__ . '/../autoload.php';


class Route
{
    /**
     * Methods
     */
    const GET    = 'GET';
    const POST   = 'POST';
    const PUT    = 'PUT';
    const DELETE = 'DELETE';
    
    /**
     * Filter inputs
     */
    const EMAIL   = FILTER_SANITIZE_EMAIL;
    const FLOAT   = FILTER_SANITIZE_NUMBER_FLOAT;
    const INTEGER = FILTER_SANITIZE_NUMBER_INT;
    const STRING  = FILTER_SANITIZE_STRING;
    const HTML    = FILTER_SANITIZE_FULL_SPECIAL_CHARS;
    const BOOLEAN = '/.+/'; // This is a preg_match operation instead of sanitize

    /**
     * @var array Saves all the roots
     */
    static $routes = [];
    /**
     * @var array Saves the last route
     */
    static $last_route;
    
    static $matches = [];

    /**
     * Method add.
     *
     * Adds route(s)
     *
     * @param string $method  Which method, Route::GET, POST, PUT, DELETE should be used
     * @param string $url     Route name (to be replaced by pattern)
     * @param string $action  Controller at action
     * @param array  $params  Transferred from user
     */
    public static function add($method, $url, $action, $params = array()){
        // Transform url to url pattern
        $url = preg_replace('/\//', '\/', preg_replace('/\{(\w+)\}/','(?<$1>[\w\.\-]+)', $url));
        $route = [
            'method' => $method,
            'url'    => $url,
            'action' => $action,
            'params' => $params
        ];
        array_push(self::$routes, $route);
    }

    public static function verify($method, $url){
        foreach(self::$routes as $route){
            if($method == $route['method'] and preg_match("/^{$route['url']}$/", $url, $matches)){
                self::$last_route = $route;
                self::$matches = $matches;
                return true;
            }
        }
        return false;
    }

    public static function load($verified){
        if($verified){
            // Transfer request to the controller and then method specific to the REQUEST_METHOD and QUERY_STRING
            $tmp = explode('@', self::$last_route['action']);
            $controller = $tmp[0];
            $action     = $tmp[1];
            unset($tmp);
            $controller_class = '\\AWorDS\\App\\Controllers\\' . $controller;
//                 var_dump(self::$matches);
            if(class_exists($controller_class)){
                if(method_exists($controller_class, $action)) (new $controller_class($controller, $action, self::$last_route['method'], self::$last_route['params'], self::$matches))->$action();
                else die("Error: Missing $controller::$action method.");
            } else die("Error: Missing $controller Controller."); // FIXME should return 404 or error
            return;
        }
        // TODO: 404 Error
        return;
    }
}