<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 6/11/18
 * Time: 5:09 AM
 */
/**
 * Set the possible values of self::$_var
 * @var bool        $logged_in
 * @var string|null $active_tab Default is null, not need if user isn't logged in
 * @var string      $title      Site title (Default: Config::SITE_TITLE)
 * @var int         $status     HttpStatusCode constant (default: HttpStatusCode::__default)
 */
/**
 * Special Files & Directories
 * @var string $__commonsDir Common view dir
 * @var string $__viewDir    View dir
 * @var string $__actionView Action view inside the Controller folder
 */
use \ADACT\App\HttpStatusCode;
$__defaultHeader  = $__commonsDir . '/header.php';         // Default header view
$__defaultFooter  = $__commonsDir . '/footer.php';         // Default footer view
$__header         = $__viewDir . '/header.php';            // Controller specific header view
$__footer         = $__viewDir . '/footer.php';            // Controller specific footer view
// Set active tab, if not already
if(!isset($active_tab)) $active_tab = null;
// Set title, if not already
if(!isset($title)) $title = \ADACT\Config::SITE_TITLE;
// Set status to default is not set
if(!isset($status)) $status = HttpStatusCode::__default;
// Print header
if(file_exists($__header)) /** @noinspection PhpIncludeInspection */
    require_once $__header;
else if(file_exists($__defaultHeader)) /** @noinspection PhpIncludeInspection */
    require_once $__defaultHeader;

// Container begin
print "<div class=\"container\">\n";

// Print action based on Http status
switch ($status){
    case HttpStatusCode::NOT_FOUND:
        /** @noinspection PhpIncludeInspection */
        require_once __DIR__ . '/../__ErrorDocuments/404Error.php';
        break;
    default:
        if(file_exists($__actionView)){
            /** @noinspection PhpIncludeInspection */
            require_once $__actionView;
        }
}
// Print header
if(file_exists($__footer)) /** @noinspection PhpIncludeInspection */
    require_once $__footer;
else if(file_exists($__commonsDir)) /** @noinspection PhpIncludeInspection */
    require_once $__defaultFooter;

// Container end
print <<< EOF
</div>

EOF;
