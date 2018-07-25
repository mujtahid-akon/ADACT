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

if($logged_in):
?>

<script>
    // Checks for notifications in every 10 seconds.
    $(document).ready(function(){
        Project.notification_handler();
        setInterval(function(){
            Project.notification_handler();
        }, 60000);
    });
</script>
<nav role="navigation" class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <button type="button" data-target="#navbar_collapse" data-toggle="collapse" class="navbar-toggle">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <sup class="notification_count visible-xs" style="color: orangered;border-radius: 2px;padding: 1px;position: absolute;right: 15px;top: 10px;"></sup>
            <a href="./home" class="navbar-brand"><?php print $title ?></a>
            <a class="btn btn-default navbar-left <?php if($active_tab == 'new') print "active" ?>" href="./projects/new" style="margin-top: 8px;color: crimson !important;">New Project</a>
        </div>
        <div id="navbar_collapse" class="collapse navbar-collapse nav navbar-nav navbar-right">
            <ul class="nav navbar-nav">
                <li>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><b class="hidden-xs glyphicon glyphicon-bell"></b><span class="visible-xs"><span class="notification_count">0</span> Notifications</span><sup class='notification_count'></sup></a>
                    <ul id="notification_bar" role="menu" class="dropdown-menu" style="width: max-content;"></ul>
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
                        <!--li><a href="#">Change Info</a></li-->
                        <li class="divider"></li>
                        <li><a href="#" style="color: #a94442;">Delete Account</a></li>
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
<nav role="navigation" class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <a href="./home" class="navbar-brand"><?php print $title; ?></a>
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