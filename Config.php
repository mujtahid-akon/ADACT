<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 4/10/17
 * Time: 10:23 PM
 */

namespace AWorDS;

define('PROJECT_DIRECTORY', __DIR__ . '/Projects');

interface Config{
  /**
  * DB Config
  */
  const MYSQL_HOST = '127.0.0.1';
  const MYSQL_USER = 'root';
  const MYSQL_PASS = 'root';
  const MYSQL_PORT = '3306';
  const MYSQL_DB   = 'awords';
  
  /**
   * Site Related Configs
   */
  const SITE_TITLE   = 'AWorDS';         // Set Site title
  const USE_ONLY_SSL = false;
  
  /**
   * These constants should not be changed
   */
  
  const PROJECT_DIRECTORY = PROJECT_DIRECTORY;

  const WORKING_DIRECTORY = '/tmp';
  
  const WEB_DIRECTORY     = '/';

  /**
   * TODO AND NOTE: File upload Limit isn't set yet
   */
  const MAX_UPLOAD_SIZE = 100000000;    // 100MB

  const MAX_FILE_SIZE   = 20000000;     // 20MB
}
