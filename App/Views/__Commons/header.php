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
<div class="pre-loader" style="color: #4b4e53">
    <i class="fa fa-spinner fa-pulse middle"></i>
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
</div>
<?php
if($logged_in):
?>
<script defer>
    $(document).ready(function(){
        // Add notification counter for small devices
        $('#nav-side-header').prepend('<sup class="notification-count dev-sm-header"></sup>');
        // Checks for notifications in every 1 minute.
        Project.notification_handler();
        setInterval(function(){
            Project.notification_handler();
        }, 60000);
    });
</script>
<nav id="top_nav" role="navigation" class="navbar navbar-expand-sm nav-side nav-side-touch nav-side-width-full">
    <div class="container">
        <div class="navbar-header">
            <a href="./home" class="navbar-brand adact-title">
                <img class="logo" src="./logos/ADACT_Logo@24x.png" aria-hidden="true" />
                <?php print $title ?>
            </a>
            <a class="btn button small orange navbar-left <?php if($active_tab == 'new') print "active" ?>" href="./projects/new" style="margin-top: 8px;">New Project</a>
        </div>
        <div class="nav navbar-nav navbar-right">
            <ul class="nav navbar-nav">
                <li class="nav-item dropdown notification-bar">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#"><b class="hidden-xs fa fa-bell"></b><sup class='hidden-xs notification-count dev-md-navbar'></sup><span class="notification-count dev-sm-navbar"></span></a>
                    <ul id="notification_bar" role="menu" class="dropdown-menu"></ul>
                </li>
                <li class="nav-item<?php if($active_tab == 'home') print " active" ?>">
                    <a class="nav-link" href="./home">Home</a>
                </li>
                <li class="nav-item dropdown disable<?php if($active_tab == 'projects') print " active" ?>">
                    <a class="nav-link inline-right" href="./projects">Projects&nbsp;</a>
                    <a  class="dropdown-toggle inline-left hidden-xs" data-toggle="dropdown" href="#"><b class="caret"></b></a>
                    <ul role="menu" class="dropdown-menu">
                        <li><a class="dropdown-item" href="./projects/new">New Project</a></li>
                        <li><a class="dropdown-item" href="./projects">All Projects</a></li>
                        <li><a class="dropdown-item" href="./projects/pending">Pending Projects</a></li>
                    </ul>
                </li>
                <li class="nav-item <?php if($active_tab == 'settings') print "active " ?>dropdown disable">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Settings <b class="caret"></b></a>
                    <ul role="menu" class="dropdown-menu">
                        <li><a class="dropdown-item" href="./reset_pass">Change Password</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./logout">Logout</a>
                </li>
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
                <img class="logo" src="./logos/ADACT_Logo@24x.png" aria-hidden="true" />
                <?php print $title; ?>
            </a>
            <a class="navbar-brand hidden-sm hidden-md hidden-lg small" href="./login">Login</a>
            <a class="navbar-brand hidden-sm hidden-md hidden-lg small" href="./reg">Register</a>
        </div>
        <div class="collapse navbar-collapse nav navbar-nav navbar-right">
            <ul class="nav navbar-nav">
                <li <?php if($active_tab == 'login') print $active ?>><a href="./login">Login</a></li>
                <li <?php if($active_tab == 'reg') print $active ?>><a href="./reg">Register</a></li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>
