<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/19/2017
 * Time: 9:23 PM
 */

namespace ADACT\App\Views;

class Template {
    const HEAD_FILE = 'head.php';
    const BODY_FILE = 'body.php';
    protected $_var = array();
    protected $_controller;
    protected $_action;

    function __construct($controller, $action) {
        $this->_controller = $controller;
        $this->_action = $action;
    }

    /**
     * Set Variables
     * @param $name
     * @param $value
     */
    function set($name, $value) {
        /** @var string $name */
        $this->_var[$name] = $value;
    }

    /**
     * render method.
     *
     * Default template for ADACT:
     *  It looks for header, footer, extra and {action} files at Views/{Controller}/ directory
     *  If a file not found, the default will be used instead (located at Views/Defaults/)
     * NOTE: headers and footers are hidden if 'logged_in' variable is not set using the Template::set()
     * More uses: (these variables are set using Template::set()
     * - active_tab : Set active tab in the navigation menu
     * - title      : Set site title (Default: Config::SITE_TITLE)
     * - status     : Set status code using HttpStatusCode
     * @throws \Exception
     */
    function render(){
        extract($this->_var);
        /**
         * Extracted default variable(s)
         * @var bool $standalone Whether to render standalone HTML or not
         */
        $__controller     = $this->_controller;
        $__action         = $this->_action;
        $__commonsDir     = __DIR__ . '/__Commons';                // Common view
        $__viewDir        = __DIR__ . '/' . $__controller;         // Controller specific view directory
        $__actionView     = $__viewDir . '/' . $__action . '.php'; // Controller specific action view
        $__standaloneHTML = isset($standalone) AND $standalone ? true : false;

        // First check whether standalone html is requested
        if($__standaloneHTML){
            // Include only the $action view
            /** @noinspection PhpIncludeInspection */
            require_once $__actionView;
            return;
        }
        // Continue otherwise

        // Common head: Required
        $__common_head = $__viewDir . '/' . self::HEAD_FILE;
        if(!file_exists($__common_head)) $__common_head = $__commonsDir . '/' . self::HEAD_FILE;
        if(!file_exists($__common_head)) throw new \Exception('No common head found!', 6243);
        // Specific head: Optional
        $__specific_head = $__viewDir . '/' . $this->_action . '.head.php';
        // Common/Special body: Optional
        $__common_body = $__viewDir . '/' . self::BODY_FILE;
        if(!file_exists($__common_body)) $__common_body = $__commonsDir . '/' . self::BODY_FILE;

        print "<!DOCTYPE html>\n";
        print "<html>\n";
        print "  <head>\n";
            /** @noinspection PhpIncludeInspection */
            require_once $__common_head;
            if(file_exists($__specific_head)):
                /** @noinspection PhpIncludeInspection */
                require_once $__specific_head;
            endif;
        print "  </head>\n";
        print "  <body>\n";
            if(file_exists($__common_body)) {
                /** @noinspection PhpIncludeInspection */
                require_once $__common_body;
            } else {
                /** @noinspection PhpIncludeInspection */
                require_once $__actionView;
            };
        print "  </body>\n";
        print "</html>\n";
    }
}
