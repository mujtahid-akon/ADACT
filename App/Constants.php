<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 5/26/2017
 * Time: 7:20 PM
 */

namespace AWorDS\App;

require_once __DIR__ . '/../autoload.php';

class Constants{
    const COUNT_ONE  = 1;
    const BOOL_TRUE  = 1;
    const BOOL_FALSE = 0;
    const ACTIVATION_KEY_LENGTH = 16;   // Can be up to 50
    const SHORTAGE_OF_ARGUMENTS = 100;
    
    /**
     * Session related constants
     */
    const SESSION_COOKIE  = 'cookie';
    const SESSION_SESSION = 'session';
    const SESSION_COOKIE_TIME = 604800;    // 7 days
    
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
     * File upload related constants
     */
    const FILE_SIZE_EXCEEDED  = 1;
    const FILE_INVALID_MIME   = 2;
    const FILE_INVALID_FILE   = 3;
    const FILE_UPLOAD_FAILED  = 4;
    const FILE_UPLOAD_SUCCESS = 0;
    
    /**
     * Project related constants
     */
    const PROJECT_DELETE_SUCCESS = 0;
    const PROJECT_DELETE_FAILED  = 1;
    const PROJECT_DOES_NOT_EXIST = 2;

    const PROJECT_TYPE_FILE    = 'file';
    const PROJECT_TYPE_GIN     = 'gin';
    const PROJECT_TYPE_ACCN    = 'accn';
    const PROJECT_TYPE_UNIPROT = 'uniprot';

    /**
     * Export related constants
     */
    const EXPORT_SPECIES_RELATION = 1;
    const EXPORT_DISTANT_MATRIX   = 2;
    const EXPORT_NEIGHBOUR_TREE   = 3;
    const EXPORT_UPGMA_TREE       = 4;
    const EXPORT_ALL              = 0;
    
    /**
     * File related constants
     */
//    const SPECIES_ORDER    = 'SpeciesOrder.txt';
    const SPECIES_RELATION = 'SpeciesRelation.txt';
    const DISTANT_MATRIX   = 'Output.txt';
    const NEIGHBOUR_TREE   = 'Neighbour tree.jpg';
    const UPGMA_TREE       = 'UPGMA tree.jpg';
    const CONFIG_JSON      = 'config.json';
    const CONFIG_TEXT      = 'config.txt';  // Configurations in a readable format: including the short names
}
