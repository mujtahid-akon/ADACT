<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 1/26/18
 * Time: 11:19 AM
 */

namespace ADACT\App\Models;

use \Exception;

class FileException extends Exception{
    /* Exception codes */
    const E_FILE_DOES_NOT_EXIST = 1;
    const E_DIRECTORY_DOES_NOT_EXIST = 2;
    const E_CONFIG_FILE_NOT_FOUND = 3;
    const E_FILE_FORMAT_ERROR = 4;

    const E_INVALID_STORE_FLAG = 10;
    const E_COULD_NOT_WRITE_TO_FILE = 12;
}
