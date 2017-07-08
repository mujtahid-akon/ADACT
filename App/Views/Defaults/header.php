<?php $active = "class=\"active\""; ?>
<nav role="navigation" class="navbar navbar-default">
    <div class="navbar-header">
        <button type="button" data-target="#navbar_collapse" data-toggle="collapse" class="navbar-toggle">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a href="home" class="navbar-brand"><?php print $title ?></a>
        <a class="btn btn-default <?php if($active_tab == 'new') print "active" ?>" href="projects/new" style="margin-top: 8px;color: crimson;">New Project</a>
        <!--ul class="nav navbar-nav">
            <li <//?php if($active_tab == 'new') print $active ?>><a href="projects/new">New Project</a></li>
        </ul-->
    </div>
    <div id="navbar_collapse" class="collapse navbar-collapse nav navbar-nav navbar-right">
        <ul class="nav navbar-nav">
            <li <?php if($active_tab == 'home') print $active ?>><a href="home">Home</a></li>
            <li <?php if($active_tab == 'projects') print $active ?>><a href="projects">All Projects</a></li>
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">Settings <b class="caret"></b></a>
                <ul role="menu" class="dropdown-menu">
                    <li><a href="reset_pass">Change Password</a></li>
                    <li><a href="#">Change Info</a></li>
                    <li class="divider"></li>
                    <li><a href="#" style="color: #a94442;">Delete Account</a></li>
                </ul>
            </li>
            <li><a href="logout">Logout</a></li>
        </ul>
    </div>
</nav>