<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 4/10/17
 * Time: 10:23 PM
 */

namespace ADACT;

define('PROJECT_DIRECTORY', Config::ROOT_DIRECTORY . '/Projects');
define('URL_SEPARATOR', (php_sapi_name() == 'cli-server') ? "?" : "&"); // DON'T CHANGE THIS!!!


interface Config{
    /* DB Config */
    const MYSQL_HOST = '127.0.0.1';
    const MYSQL_PORT = '3306';
    const MYSQL_USER = 'root';
    const MYSQL_PASS = 'root';
    const MYSQL_DB   = 'awords';

    /* MAIL Config (SMTP) */
    /** SMTP server address */
    const MAIL_HOST = 'smtp.gmail.com';
    /** SMTP server port */
    const MAIL_PORT = 587;
    /** SMTP server security: tls or ssl */
    const MAIL_SECURITY  = 'tls';
    const MAIL_SMTP_AUTH = true;
    /** SMTP username (e.g. muntashir.islam96@gmail.com for GMail) */
    const MAIL_USER = 'example@gmail.com';
    /** SMTP password */
    const MAIL_PASS = 'example_pass';
    /** Email address (which would be shown in the from section) */
    const MAIL_FROM = 'example@gmail.com';
    /** Name associated with the email (which would be shown in the from section, ignored by some mail client though) */
    const MAIL_NAME = 'Info at ADACT';

    /* Site Related Configs */
    /** Site title, as appeared in the title and the banner section */
    const SITE_TITLE   = 'ADACT';
    /** Title & address of the organisation */
    const ORG_ADDRESS  = 'Bangladesh University of Engineering & Technology, Dhaka, Bangladesh';
    /** Full Website address, including sub-domain, port (if other than 80), and the URI to the project root */
    const WEB_ADDRESS  = 'http://127.0.0.1:8080';
    /** Whether to use HTTPS only instead of both HTTP and HTTPS */
    const USE_ONLY_SSL = false;
    /** Whether to enable debug mode */
    const DEBUG_MODE   = true;

    /* The constants below should not be changed unless absolutely necessary */
    /**
     * Base directory. Usually set to '/',
     * but may change depending on how the `./public` directory
     * is kept on the server
     */
    const WEB_DIRECTORY = '/';

    /* Various Limits */
    /** Maximum file upload limit: default is 100 MB */
    const MAX_UPLOAD_SIZE  = 100000000;
    /** Maximum FASTA file size: default is 20 MB */
    const MAX_FILE_SIZE    = 20000000;
    /** Maximum number of FASTA files allowed TODO */
    const MAX_FILE_ALLOWED = 30;
    /**
     * Maximum number of characters
     *
     * FIXME: Changing this may not change maximum allowed character in some places
     */
    const MAX_CHAR_ALLOWED = 15;

    /* DO NOT CHANGE ANYTHING AFTER THIS LINE */
    /** Project root directory */
    const ROOT_DIRECTORY = __DIR__;
    /** Project directory, same as PROJECT_DIRECTORY global constant */
    const PROJECT_DIRECTORY = PROJECT_DIRECTORY;
    /** Working directory */
    const WORKING_DIRECTORY = self::ROOT_DIRECTORY . '/tmp';

    static function mysqli();
    static function email($name, $email, $subject, $message);
    static function formatted_email($name, $email, $subject, $body);
    function login_check();
}
