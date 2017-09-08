<?php
/**
 * From the Controller class
 *
 * @var string $title
 * @var string $active_tab
 */
$active = "class=\"active\"";
// Get unread projects
$unread_projects = (new \AWorDS\App\Models\Project())->unread_projects_info();
$unread_projects_count = count($unread_projects);

$unread_projects_list = [];

foreach ($unread_projects as $project){
    $row = "<li><a href='/projects/{$project['id']}'>#{$project['id']} {$project['name']}</a></li>";
    array_push($unread_projects_list, $row);
}
?>
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
                <li><a data-toggle="dropdown" class="dropdown-toggle" href="#"><b class="glyphicon glyphicon-bell"></b><?php if($unread_projects_count > 0) print "<sup class='unread-count'>{$unread_projects_count}</sup>"; ?></a>
                    <?php if($unread_projects_count > 0): ?>
                    <ul role="menu" class="dropdown-menu">
                        <?php print implode("\n", $unread_projects_list); ?>
                    </ul>
                    <?php else: ?>
                    <div role="menu" class="dropdown-menu">
                        <div style="padding: 5px 10px"><em>No new notifications.</em></div>
                    </div>
                    <?php endif; ?>
                </li>
                <li <?php if($active_tab == 'home') print $active ?>><a href="/home">Home</a></li>
                <li class="<?php if($active_tab == 'projects') print "active " ?>dropdown">
                    <a href="/projects" style="display: inline-block;">Project</a><a data-toggle="dropdown" class="dropdown-toggle" href="#"  style="display: inline-block;padding-left: 0"><b class="caret"></b></a>
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