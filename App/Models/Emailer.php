<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 1/27/18
 * Time: 10:39 AM
 */

namespace ADACT\App\Models;

require_once __DIR__ . '/../../Libraries/PHPMailer/PHPMailerAutoload.php';

use ADACT\Config;

class Emailer implements Config {
    private $mail;

    public function __construct($name = null, $email = null, $subject = null, $message = null){
        // Create a new PHPMailer instance
        $this->mail = new \PHPMailer;
        // Tell PHPMailer to use SMTP
        $this->mail->isSMTP();
        // Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $this->mail->SMTPDebug = Config::DEBUG_MODE ? 0 : 0;
        // Ask for HTML-friendly debug output
        // $mail->Debugoutput = 'html';
        // Set the hostname of the mail server
        $this->mail->Host = self::MAIL_HOST;
        // use
        // $mail->Host = gethostbyname(self::MAIL_HOST);
        // if your network does not support SMTP over IPv6
        // Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->mail->Port = self::MAIL_PORT;
        // Set the encryption system to use - ssl (deprecated) or tls
        $this->mail->SMTPSecure = self::MAIL_SECURITY;
        // Whether to use SMTP authentication
        $this->mail->SMTPAuth = self::MAIL_SMTP_AUTH;
        // Username to use for SMTP authentication - use full email address for gmail
        $this->mail->Username = self::MAIL_USER;
        // Password to use for SMTP authentication
        $this->mail->Password = self::MAIL_PASS;
        // Set who the message is to be sent from
        $this->mail->setFrom(self::MAIL_FROM, self::MAIL_NAME);
        // Set an alternative reply-to address
        // $mail->addReplyTo('info@example.com', self::MAIL_NAME);
        // Set who the message is to be sent to
        if($email != null) $this->mail->addAddress($email, $name);
        // Set the subject line
        $this->mail->Subject = $subject;
        // Read an HTML message body from an external file, convert referenced images to embedded,
        // convert HTML into a basic plain-text alternative body
        if($message != null) $this->mail->msgHTML($message, dirname(__FILE__));
        // Replace the plain text body with one created manually
        // $this->mail->addAttachment('');
        // send the message, check for errors
    }

    public function setAddress($email, $name){
        $this->mail->addAddress($email, $name);
    }

    public function setFrom($email, $name){
        $this->mail->setFrom($email, $name);
    }

    public function setSubject($subject){
        $this->mail->Subject = $subject;
    }

    public function setMessage($message, $isHTML = true, $basedir = __DIR__){
        $this->mail->isHTML($isHTML);
        if($isHTML) $this->mail->msgHTML($message, $basedir);
        else $this->mail->Body = $message;
    }

    public function addAttachment($file){
        $this->mail->addAttachment($file);
    }

    public function send(){
        if(!$this->mail->send()){
            if(self::DEBUG_MODE) error_log("Mailer Error: " . $this->mail->ErrorInfo);
            return false;
        }else{
            return true;
        }
    }

    public static function button($text, $link){
        return <<< EOF
<a target="_blank" href="{$link}" style="
    background: blue;
    color: #ddd;
    padding: 10px 20px;
    margin: 10px;
    display: inline-block;
    text-decoration: unset;
    border: 1px solid;
    border-radius: 3px;
    font-family: sans-serif;
    text-align: center;
">{$text}</a>
EOF;

    }
}