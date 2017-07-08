<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/19/2017
 * Time: 9:24 PM
 */
namespace AWorDS\App\Models;

require_once __DIR__ . '/../../autoload.php';

use \AWorDS\Config;
use \AWorDS\App\Constants;

class Model implements Config
{
    public $mysqli;

    function __construct(){
        $this->mysqli = new \Mysqli(self::MYSQL_HOST, self::MYSQL_USER, self::MYSQL_PASS, self::MYSQL_DB, self::MYSQL_PORT);
        if ($this->mysqli->connect_error) {
            die("Unable to connect to mysql.");
        }
        $this->mysqli->set_charset("utf8");
    }
        
    static function mysqli(){
        $mysqli = new \Mysqli(self::MYSQL_HOST, self::MYSQL_USER, self::MYSQL_PASS, self::MYSQL_DB, self::MYSQL_PORT);
        if ($mysqli->connect_error) {
            die("Unable to connect to mysql.");
        }
        $mysqli->set_charset("utf8");
        return $mysqli;
    }
    
    static function email($to, $subject, $message){
        // To send HTML mail, the Content-type header must be set
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';

        // Additional headers
        $headers[] = 'From: AWorDS <info@awords.co.uk>'; // TODO: use constant

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
    
    
    function login_check(){
        if(isset($_SESSION['session'])){ // Top priority
            if($stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM `active_sessions` WHERE `user_id` = ? AND `session_id` = ? AND `type` = "session"')){
                $stmt->bind_param('is', $_SESSION['session']['id'], session_id());
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($count);
                $stmt->fetch();
                if($count == Constants::COUNT_ONE){
                    // Make things easy and quick by setting session
                    $_SESSION['user_id'] = $_SESSION['session']['id'];
                    return true;
                }
            }
        }else{  // Cookie
            if(isset($_COOKIE['u_id'], $_COOKIE['id'])){
                if($stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM `active_sessions` WHERE `user_id` = ? AND `session_id` = ? AND `type` = "cookie"')){
                    $stmt->bind_param('is', $_COOKIE['u_id'], $_COOKIE['id']);
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($count);
                    $stmt->fetch();
                    if($count == Constants::COUNT_ONE){
                        // Make things easy and quick by setting session
                        $_SESSION['user_id'] = $_COOKIE['u_id'];
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
}