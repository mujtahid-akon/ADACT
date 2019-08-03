<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/19/2017
 * Time: 9:24 PM
 */
namespace ADACT\App\Models;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../Libraries/PHPMailer/PHPMailerAutoload.php';

use \ADACT\Config;

class Model implements Config {
    public $mysqli;

    function __construct(){
        @$this->mysqli = new \Mysqli(self::MYSQL_HOST, self::MYSQL_USER, self::MYSQL_PASS, self::MYSQL_DB, self::MYSQL_PORT);
        if ($this->mysqli->connect_error) {
            error_log("MySQL: [{$this->mysqli->connect_errno}] {$this->mysqli->connect_error}");
        }
        @$this->mysqli->set_charset("utf8");
    }
        
    static function mysqli(){
        @$mysqli = new \Mysqli(self::MYSQL_HOST, self::MYSQL_USER, self::MYSQL_PASS, self::MYSQL_DB, self::MYSQL_PORT);
        if ($mysqli->connect_error) {
            error_log("MySQL: [{$mysqli->connect_errno}] {$mysqli->connect_error}");
        }
        @$mysqli->set_charset("utf8");
        return $mysqli;
    }

    /**
     * // TODO replace w/ Emailer class
     * @param string $name
     * @param string $email
     * @param string $subject
     * @param string $message
     * @return bool
     * @throws \phpmailerException
     */
    static function email($name, $email, $subject, $message){
        //Create a new PHPMailer instance
        $mail = new \PHPMailer;
        //Tell PHPMailer to use SMTP
        $mail->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = Config::DEBUG_MODE ? 0 : 0;
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
        //send the message, check for errors
        if(!$mail->send()){
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }else{
            return true;
        }
    }

    /**
     * @param $name
     * @param $email
     * @param $subject
     * @param $body
     * @return bool
     * @throws \phpmailerException
     */
    static function formatted_email($name, $email, $subject, $body){
        $site_title = self::SITE_TITLE;
        $address = self::ORG_ADDRESS;
        $year = date('Y');
        $date = date('M d, Y');
        $message = <<< EOF
<!DOCTYPE html>
<html>
<head>
  <title>{$subject}</title>
</head>
<body style="margin: 15px auto;width: 650px;color: #555;">
  <header>
    <h1 style="display: inline-block;margin: auto;font-family: monospace;font-weight: normal;color: #777;">{$site_title}</h1>
    <date style="text-align: right;display: inline-block;float: right;position: relative;top: 11px;">{$date}</date>
  </header>
  <section style="padding: 5px 10px;box-shadow: #eee 0 2px 5px 3px;">
    <p>Dear {$name},</p>
    {$body}
    <p>Regards,</p>
    <p>{$site_title} Team</p>
  </section>
  <footer style="color: #aaa;font-family: sans-serif;padding-top: 10px;">
    <section>
      <small>You received this mail because you created an account at ADACT.</small>
    </section>
    <section>
      <small>&copy; {$year} {$site_title}, {$address}</small>
    </section>
  </footer>
</body>
</html>
EOF;
        return self::email($name === "User" ? '' : $name, $email, $subject, $message);
    }
    
    
    function login_check(){
        $session_id = session_id();
        if($session_id){
            if(@$stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM `active_sessions` WHERE `user_id` = ? AND `session_id` = ?')){
                $stmt->bind_param('is', $_SESSION['user_id'], $session_id);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($count);
                $stmt->fetch();
                if($count == 1) return true;
            }
        }
        return false;
    }

    function __destruct(){
        @$this->mysqli->close();
    }
}
