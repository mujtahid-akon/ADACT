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
use PHPMailer;
use phpmailerException;

class Emailer extends Model {
    private $mail;

    /**
     * Emailer constructor.
     * @param string|null $name     Name of the user
     * @param string|null $email    Email of the user
     * @param string|null $subject  Email's subject
     * @param string|null $message  Email body
     * @throws phpmailerException
     */
    public function __construct($name = null, $email = null, $subject = null, $message = null){
        parent::__construct();
        // Create a new PHPMailer instance
        $this->mail = new PHPMailer(true);
        // Tell PHPMailer to use SMTP
        $this->mail->isSMTP();
        // Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $this->mail->SMTPDebug = Config::DEBUG_MODE ? 0 : 0;
        // Set the hostname of the mail server
        $this->mail->Host = self::MAIL_HOST;
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
        // Set who the message is to be sent to
        if($email != null) $this->mail->addAddress($email, $name);
        // Set the subject line
        $this->mail->Subject = $subject;
        // Read an HTML message body from an external file, convert referenced images to embedded,
        // convert HTML into a basic plain-text alternative body
        if($message != null) $this->mail->msgHTML($message, dirname(__FILE__));
    }

    /**
     * Set the `to` section of the email
     *
     * @param string $email
     * @param string $name
     */
    public function setAddress($email, $name){
        $this->mail->addAddress($email, $name);
    }

    /**
     * Set the `from` section of the email
     *
     * @param string $email
     * @param string $name
     * @throws phpmailerException
     */
    public function setFrom($email, $name){
        $this->mail->setFrom($email, $name);
    }

    /**
     * Set the subject of the email
     *
     * @param $subject
     */
    public function setSubject($subject){
        $this->mail->Subject = $subject;
    }

    /**
     * Set the body section of the email
     * @param string $message Email body (with or without HTML tags)
     * @param bool   $isHTML  Whether the message is a HTML
     * @param string $basedir Base directory of the file (ie. Model)
     */
    public function setMessage($message, $isHTML = true, $basedir = __DIR__){
        $this->mail->isHTML($isHTML);
        if($isHTML) $this->mail->msgHTML($message, $basedir);
        else $this->mail->Body = $message;
    }

    /**
     * Add attachment
     * @param string $file
     * @throws phpmailerException
     */
    public function addAttachment($file){
        $this->mail->addAttachment($file);
    }

    /**
     * Send Email
     *
     * TODO: This fn will save the email to the database instead of sending it
     *       as emails will be sent using Cron Job in future.
     *
     * @return bool
     * @throws phpmailerException
     */
    public function send(){
//        if($stmt = $this->mysqli->prepare('INSERT INTO emails (name, email, subject, message) VALUE (?, ?, ?, ?)')){
//            $stmt->bind_param('ssss', $this->name, $this->email, $this->subject, $this->message);
//            $stmt->execute();
//            $stmt->store_result();
//            if($stmt->affected_rows == 1) return true;
//        }
//        return false;

        try {
            if (!$this->mail->send()) {
                if (self::DEBUG_MODE) error_log("Mailer Error: " . $this->mail->ErrorInfo);
                return false;
            } else {
                return true;
            }
        } catch (phpmailerException $e) {
            if (self::DEBUG_MODE) error_log("Mailer Error: " . $e->getMessage());
            if($e->getCode() == PHPMailer::STOP_CRITICAL) {
                // A critical error has occurred.
                error_log("PhpMailer: {$e->getMessage()} in ". __FILE__);
                // TODO: Parse relevant errors, ie. smtp_connect_failed, from_failed, data_not_accepted, recipients_failed
                throw $e;
            }
            // Otherwise try sending again
            return $this->send();
        }
    }

    /**
     * Get emails from database and send them
     * TODO: In future, only this function will use PhpMailer to manipulate data and send emails.
     * @throws phpmailerException
     */
    public function sendEmails(){
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        // Tell PHPMailer to use SMTP
        $mail->isSMTP();
        // Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = Config::DEBUG_MODE ? 0 : 0;
        // Set the hostname of the mail server
        $mail->Host = self::MAIL_HOST;
        // Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = self::MAIL_PORT;
        // Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPSecure = self::MAIL_SECURITY;
        // Whether to use SMTP authentication
        $mail->SMTPAuth = self::MAIL_SMTP_AUTH;
        // Username to use for SMTP authentication - use full email address for gmail
        $mail->Username = self::MAIL_USER;
        // Password to use for SMTP authentication
        $mail->Password = self::MAIL_PASS;
        // Set who the message is to be sent from
        $mail->setFrom(self::MAIL_FROM, self::MAIL_NAME);
        if($stmt = $this->mysqli->prepare('SELECT id, name, email, subject, message FROM emails WHERE 1')){
            $stmt->execute();
            $stmt->store_result();
            for($i = 0; $i < $stmt->num_rows; ++$i){
                $stmt->bind_result($id, $name, $email, $subject, $message);
                $stmt->fetch();
                // Set who the message is to be sent to
                $mail->addAddress($email, $name);
                // Set the subject line
                $mail->Subject = $subject;
                // Read an HTML message body from an external file, convert referenced images to embedded,
                // convert HTML into a basic plain-text alternative body
                $mail->msgHTML($message, dirname(__FILE__));
                if (!$mail->send()) { // TODO: Handle critical errors
                    if (self::DEBUG_MODE) error_log("Mailer Error: " . $mail->ErrorInfo);
                }else{
                    // Remove email from list as the email was sent successfully
                    if($r_stmt = $this->mysqli->prepare('DELETE FROM emails WHERE id = '.$id)){
                        $r_stmt->execute();
                        $r_stmt->store_result();
                    }
                }
            }
        }
    }

    /**
     * Add a button in the HTML format mail
     * @param $text
     * @param $link
     * @return string
     */
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