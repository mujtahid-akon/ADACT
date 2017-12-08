<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/19/2017
 * Time: 9:23 PM
 */

namespace ADACT\App\Views;

use ADACT\App\HttpStatusCode;
use \ADACT\Config;

class Template
{
    protected $_var = array();
    protected $_controller;
    protected $_action;
    protected $_hide_header = false;
	protected $_hide_footer = false;
	
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

    function hide_header(){
        $this->_hide_header = true;
    }
	
	function hide_footer(){
        $this->_hide_footer = true;
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
     */
    function render(){
        extract($this->_var);

        /**
         * Set the possible values of self::$_var
         * @var bool        $logged_in
         * @var string|null $active_tab Default is null, not need if user isn't logged in
         * @var string      $title      Site title (Default: Config::SITE_TITLE)
         * @var int         $status     HttpStatusCode constant (default: HttpStatusCode::__default)
         */

        // Hide header and footer if not $logged_in isn't set
		if(!isset($logged_in)){
			$this->hide_header();
			$this->hide_footer();
		}

		// Set active tab, if not already
		if(!isset($active_tab)) $active_tab = null;
		// Set title, if not already
        if(!isset($title)) $title = Config::SITE_TITLE;
        // Set status to default is not set
        if(!isset($status)) $status = HttpStatusCode::__default;

        $DIR = Config::WEB_DIRECTORY;

        $default_view_dir = __DIR__ . '/Defaults/';                     // Default view directory
        $view_dir         = __DIR__ . '/' . $this->_controller . '/';   // Controller specific view directory

        $default_header = $default_view_dir . 'header.php';             // Default header view
        $default_footer = $default_view_dir . 'footer.php';             // Default footer view
        $header         = $view_dir . 'header.php';                     // Controller specific header view
        $footer         = $view_dir . 'footer.php';                     // Controller specific footer view
        $action         = $view_dir . $this->_action . '.php';          // Controller specific action view
        $extra          = $view_dir . 'extra.php';                      // Controller specific extra view

        print <<< EOF
<!DOCTYPE html>
<html lang="en">
<head>
	<title>{$title}</title>
	<meta charset="utf-8" />
	<meta name="Author" content="Mujtahid Akon" />
	<base href="{$DIR}">
	<meta name="Description" content="ADACT" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<!--link href="https://www.fontify.me/wf/7d6c4da9e6ebf1836a1c32879c63dbfc" rel="stylesheet" type="text/css" /-->
	<link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" />
	<!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous" /-->
	<link rel="stylesheet" href="css/main.css" type="text/css" />
	<script src="js/jquery.min.js"></script>
	<!--script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script-->
	<script src="js/bootstrap.min.js"></script>
	<script src="js/app.js"></script>
	<!--script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script-->
EOF;

        if(file_exists($extra)) require_once $extra; // Extra things to be added in the header section

        print <<< EOF
</head>
<body>

EOF;

        // Show header if wanted
        if(!$this->_hide_header){
            if(file_exists($header)) require_once $header;              // Header view
            else if(file_exists($default_view_dir)) require_once $default_header;
        }

        // Container begin
        print "<div class=\"container\">";

        // Show action
        if(file_exists($action)){
            if($status == HttpStatusCode::NOT_FOUND){ // Error
                print <<< EOF
    <div class="container-table">
        <div class="vertical-center-row text-center">
            <h1 class="title"><a href="/home">{$title}</a></h1>
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-4">
EOF;
                require_once $action;
                print <<< EOF
                </div>
                <div class="col-md-4"></div>
            </div>
        </div>
    </div>
EOF;
            }else{
                require_once $action;
            }
        }

        // Show header if wanted
		if(!$this->_hide_footer){
        	if(file_exists($footer)) require_once $footer;              // Footer view
        	else if(file_exists($default_view_dir)) require_once $default_footer;
		}

        // Container end
        print <<< EOF
</div>
</body>
</html>

EOF;
    }
}