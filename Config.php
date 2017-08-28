<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 4/10/17
 * Time: 10:23 PM
 */

namespace AWorDS;

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
    const MAIL_USER = 'example@gmail.com'; // Your username (e.g. muntashir.islam96@gmail.com for Gmail)
    const MAIL_PASS = 'example_password';  // Your password
    const MAIL_FROM = 'example@gmail.com'; // Your email address
    const MAIL_NAME = 'Info at AWorDS';

    /**
    * Site Related Configs
    */
    const SITE_TITLE   = 'AWorDS';         // Set Site title
    const ORG_ADDRESS  = 'Bangladesh University of Engineering & Technology, Dhaka, Bangladesh'; // Organization address
    const WEB_ADDRESS  = 'http://127.0.0.1:8080'; // Full Website address
    const USE_ONLY_SSL = false;
    const DEBUG_MODE   = true;

    /**
    * These constants should not be changed
    */

    const PROJECT_DIRECTORY = PROJECT_DIRECTORY;

    const WORKING_DIRECTORY = '/tmp';

    const WEB_DIRECTORY     = '/';

    /**
    * File upload Limit
    */
    const MAX_UPLOAD_SIZE = 100000000;    // 100MB

    const MAX_FILE_SIZE   = 20000000;     // 20MB
}
