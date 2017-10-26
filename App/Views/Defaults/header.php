<?php
/**
 * From the Controller class
 *
 * @var string $title
 * @var string $active_tab
 */
$active = "class=\"active\"";
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
            <a href="/home" class="navbar-brand"><?php print $title ?></a>
            <a class="btn btn-default navbar-left <?php if($active_tab == 'new') print "active" ?>" href="/projects/new" style="margin-top: 8px;color: crimson;">New Project</a>
        </div>
        <div id="navbar_collapse" class="collapse navbar-collapse nav navbar-nav navbar-right">
            <ul class="nav navbar-nav">
                <li>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><b class="glyphicon glyphicon-bell"></b><sup id='notification_count'></sup></a>
                    <ul id="notification_bar" role="menu" class="dropdown-menu" style="width: max-content;"></ul>
                </li>
                <li <?php if($active_tab == 'home') print $active ?>><a href="/home">Home</a></li>
                <li class="<?php if($active_tab == 'projects') print "active " ?>dropdown">
                    <a href="/projects" style="display: inline-block; padding-right: 0;">Project&nbsp;</a><a data-toggle="dropdown" class="dropdown-toggle" href="#"  style="display: inline-block;padding-left: 0"><b class="caret"></b></a>
                    <ul role="menu" class="dropdown-menu">
                        <li><a href="/projects/new" style="color: #a94442;">New Project</a></li>
                        <li class="divider"></li>
                        <li><a href="/projects">All Projects</a></li>
                        <li><a href="/projects/pending">Pending Projects</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Settings <b class="caret"></b></a>
                    <ul role="menu" class="dropdown-menu">
                        <li><a href="/reset_pass">Change Password</a></li>
                        <li><a href="#">Change Info</a></li>
                        <li class="divider"></li>
                        <li><a href="#" style="color: #a94442;">Delete Account</a></li>
                    </ul>
                </li>
                <li><a href="/logout">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>