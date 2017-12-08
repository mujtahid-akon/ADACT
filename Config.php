<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 4/10/17
 * Time: 10:23 PM
 */

namespace ADACT;

define('PROJECT_DIRECTORY', __DIR__ . '/Projects');
define('URL_SEPARATOR', (php_sapi_name() == 'cli-server') ? "?" : "&");


interface Config{
    /**
    * DB Config
    */
    const MYSQL_HOST = '127.0.0.1';
    const MYSQL_PORT = '3306';
    const MYSQL_USER = 'root';
    const MYSQL_PASS = 'root';
    const MYSQL_DB   = 'awords';

    /**
     * MAIL Config (SMTP)
     */
    const MAIL_HOST = 'smtp.gmail.com';              // SMTP server address
    const MAIL_PORT = 587;                           // SMTP server port
    const MAIL_SECURITY  = 'tls'; // OR ssl
    const MAIL_SMTP_AUTH = true;
    const MAIL_USER = 'example@gmail.com'; // Your username (e.g. muntashir.islam96@gmail.com for GMail)
    const MAIL_PASS = 'example_password';  // Your password
    const MAIL_FROM = 'example@gmail.com'; // Your email address
    const MAIL_NAME = 'Info at AWorDS';

    /**
    * Site Related Configs
    */
    const SITE_TITLE   = 'ADACT';         // Set Site title
    const ORG_ADDRESS  = 'Bangladesh University of Engineering & Technology, Dhaka, Bangladesh'; // Organization address
    const WEB_ADDRESS  = 'http://127.0.0.1:8080'; // Full Website address
    const USE_ONLY_SSL = false;
    const DEBUG_MODE   = true;

    /**
    * These constants should not be changed unless absolutely necessary
    */
    const PROJECT_DIRECTORY = PROJECT_DIRECTORY;
    const WORKING_DIRECTORY = __DIR__ . '/tmp';
    const WEB_DIRECTORY     = '/';

    /**
    * Limits
    */
    // Maximum file upload limit
    const MAX_UPLOAD_SIZE = 100000000;    // 100MB
    // Maximum FASTA file size
    const MAX_FILE_SIZE   = 20000000;     // 20MB
    // Maximum number of FASTA files that are allowed
    const MAX_FILE_ALLOWED = 30; // TODO
    // Maximum number of characters

    /**
     * DO NOT CHANGE ANYTHING AFTER THIS LINE
     */
    const ROOT_DIRECTORY = __DIR__;

    static function mysqli();
    static function email($name, $email, $subject, $message);
    static function formatted_email($name, $email, $subject, $body);
    function login_check();
}
