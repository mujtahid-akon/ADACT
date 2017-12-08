<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 5/26/2017
 * Time: 7:20 PM
 */

namespace ADACT\App;

require_once __DIR__ . '/../autoload.php';

/**
 * Class Constants
 * @package ADACT\App
 * @deprecated
 */
class Constants{
    const COUNT_ONE  = 1;
    const BOOL_TRUE  = 1;
    const BOOL_FALSE = 0;
    const ACTIVATION_KEY_LENGTH = 16;   // Can be up to 50
    const SHORTAGE_OF_ARGUMENTS = 100;

    const ACTIVE_TAB = 'active_tab';

    /**
     * Login related constants
     */
    const LOGIN_LOCKED  = 2;
    const LOGIN_SUCCESS = 0;
    const LOGIN_FAILURE = 1;
    const MAX_LOGIN_ATTEMPTS = 3;
    
    /**
     * Register related constants
     */
    const REGISTER_SUCCESS = 0;
    const REGISTER_FAILURE = 1;
    
    /**
     * Accounts related constants
     */
    CONST ACCOUNT_EXISTS = 4;
    const ACCOUNT_DOES_NOT_EXIST = 5;
    
    /**
     * Project related constants
     */
    const PROJECT_DELETE_SUCCESS = 0;
    const PROJECT_DELETE_FAILED  = 1;
    const PROJECT_DOES_NOT_EXIST = 2;

    /**
     * Export related constants
     */
    const EXPORT_SPECIES_RELATION = 1;
    const EXPORT_DISTANT_MATRIX   = 2;
    const EXPORT_NEIGHBOUR_TREE   = 3;
    const EXPORT_UPGMA_TREE       = 4;
    const EXPORT_ALL              = 0;
}
