<?php
/**
 * From the Controller class
 *
 * @var string $title
 * @var string $active_tab
 * @var bool   $logged_in
 */
$active = "class=\"active\"";
if(!isset($logged_in)) $logged_in = false;
?>
<!-- Loader -->
<div class="pre-loader" style="color: #4b4e53"><i class="fa fa-spinner fa-pulse middle"></i></div>
<?php
if($logged_in):
?>
<script>
    $(document).ready(function(){
        // Add notification counter for small devices
        $('#nav-side-header').prepend('<sup class="notification-count dev-sm"></sup>');
        // Checks for notifications in every 1 minute.
        Project.notification_handler();
        setInterval(function(){
            Project.notification_handler();
        }, 60000);
    });
</script>
<nav id="top_nav" role="navigation" class="navbar nav-side nav-side-touch">
    <div class="container">
        <div class="btn-close">&times;</div>
        <div class="navbar-header">
            <a href="./home" class="navbar-brand adact-title">
                <img class="logo" src="./logos/ADACT_Logo_black_32x32.png" aria-hidden="true" />
                <?php print $title ?>
            </a>
            <a class="btn button small orange navbar-left <?php if($active_tab == 'new') print "active" ?>" href="./projects/new" style="margin-top: 8px;">New Project</a>
        </div>
        <div class="nav navbar-nav navbar-right">
            <ul class="nav navbar-nav">
                <li>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><b class="hidden-xs fa fa-bell"></b><span class="visible-xs"><span class="notification-count">0</span> Notifications</span><sup class='notification-count'></sup></a>
                    <ul id="notification_bar" role="menu" class="dropdown-menu"></ul>
                </li>
                <li <?php if($active_tab == 'home') print $active ?>><a href="./home">Home</a></li>
                <li class="<?php if($active_tab == 'projects') print "active " ?>dropdown">
                    <a href="./projects" style="display: inline-block; padding-right: 0;">Projects&nbsp;</a><a data-toggle="dropdown" class="dropdown-toggle" href="#"  style="display: inline-block;padding-left: 0"><b class="caret"></b></a>
                    <ul role="menu" class="dropdown-menu">
                        <li><a href="./projects/new" style="color: #a94442;">New Project</a></li>
                        <li class="divider"></li>
                        <li><a href="./projects">All Projects</a></li>
                        <li><a href="./projects/pending">Pending Projects</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Settings <b class="caret"></b></a>
                    <ul role="menu" class="dropdown-menu">
                        <li><a href="./reset_pass">Change Password</a></li>
                    </ul>
                </li>
                <li><a href="./logout">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<?php else: ?>
<style>
    .small{
        font-size: 85% !important;
    }
</style>
<nav id="top_nav" role="navigation" class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <a href="./home" class="navbar-brand adact-title">
                <img class="logo" src="./logos/ADACT_Logo_black_32x32.png" aria-hidden="true" />
                <?php print $title; ?>
            </a>
            <a class="navbar-brand hidden-sm hidden-md hidden-lg small" href="./login">Login</a>
            <a class="navbar-brand hidden-sm hidden-md hidden-lg small" href="./reg">Register</a>
        </div>
        <div class="collapse navbar-collapse nav navbar-nav navbar-right">
            <ul class="nav navbar-nav">
                <li><a href="./login">Login</a></li>
                <li><a href="./reg">Register</a></li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>
<!-- Javascript Warning -->
<noscript class="container" style="display: block; text-align: center;">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-danger">
                Javascript is required to carry out certain operations.
                Please enable Javascript.
            </div>
        </div>
    </div>
</noscript>
