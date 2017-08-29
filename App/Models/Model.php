<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/19/2017
 * Time: 9:24 PM
 */
namespace AWorDS\App\Models;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../Libraries/PHPMailer/PHPMailerAutoload.php';

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
    
    static function email($name, $email, $subject, $message){
        //Create a new PHPMailer instance
        $mail = new \PHPMailer;
        //Tell PHPMailer to use SMTP
        $mail->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;
        //Ask for HTML-friendly debug output
        //$mail->Debugoutput = 'html';
        //Set the hostname of the mail server
        $mail->Host = self::MAIL_HOST;
        // use
        // $mail->Host = gethostbyname(self::MAIL_HOST);
        // if your network does not support SMTP over IPv6
        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = self::MAIL_PORT;
        //Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPSecure = self::MAIL_SECURITY;
        //Whether to use SMTP authentication
        $mail->SMTPAuth = self::MAIL_SMTP_AUTH;
        //Username to use for SMTP authentication - use full email address for gmail
        $mail->Username = self::MAIL_USER;
        //Password to use for SMTP authentication
        $mail->Password = self::MAIL_PASS;
        //Set who the message is to be sent from
        $mail->setFrom(self::MAIL_FROM, self::MAIL_NAME);
        //Set an alternative reply-to address
        //$mail->addReplyTo('info@example.com', self::MAIL_NAME);
        //Set who the message is to be sent to
        $mail->addAddress($email, $name);
        //Set the subject line
        $mail->Subject = $subject;
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $mail->msgHTML($message, dirname(__FILE__));
        //Replace the plain text body with one created manually
        //TODO Add attachment
        //$mail->addAttachment('');
        //send the message, check for errors
        if(!$mail->send()){
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }else{
            return true;
        }
    }
    
    
    function login_check(){
        if(isset($_SESSION['session'])){ // Top priority
            $session_id = session_id(); // To remove E_STRICT error
            if($stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM `active_sessions` WHERE `user_id` = ? AND `session_id` = ? AND `type` = "session"')){
                $stmt->bind_param('is', $_SESSION['session']['id'], $session_id);
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