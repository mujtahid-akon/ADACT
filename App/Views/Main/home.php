<?php
/**
 * @var array  $last_project
 * @var bool   $logged_in
 * @var string $title
 */
if($logged_in){ ?>
    <div class="row">
        <div class="col-md-12">
            <?php if($last_project['seen'] === 0){ ?>
            <div class="alert alert-info">
                <a href="projects/last">View the result of your last project.</a>
                <a href="#" class="close" data-dismiss="alert">&times;</a>
            </div>
            <?php } ?>
            <h1>Welcome!</h1>
            <h3><a href="projects/new">Create a new project (if doesn't have any project) to get started</a></h3>
            <h3><a class="h3" href="projects">Previous projects (if have any)</a></h3>
        </div>
    </div>
<?php }else{ ?>
<div class="navbar">
    <div class="navbar-inner nav">
        <div class="navbar-header">
            <div class="navbar-brand title"><?php print $title; ?></div>
        </div>
        <form class="navbar-form form-horizontal navbar-right" id="login_form" method="post" action="login">
            <input class="form-control" type="text" name="email" placeholder="E-mail address" required />
            <input class="form-control" type="password" name="pass" placeholder="Password" required />
            <label><input type="checkbox" name="remember" /> Remember me</label>
            <input class="btn btn-primary" type="submit" value="login" />
        </form>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <h1 class="title">Welcome!</h1>
        <p>
            Welcome to the Absent Word Based (Dis)similarity analysis of Sequences; AWorDS in short.
            (Pronounced like "Awards"). This tool produces the distance matrix, species relation, phylogenetic trees
            based on a number of indices.
        </p>
    </div>
    <div class="col-md-6">
        <h3>Register</h3>
        <?php 
            if(isset($_SESSION['register_error'])){
                print "<div class=\"alert alert-danger\">{$_SESSION['register_error']}</div>";
                unset($_SESSION['register_error']);
            }
        ?>
        <small class="text-danger"><em>Please fill out all the boxes.</em></small>
        <form class="form-vertical" id="reg_form" method="post" action="reg">
            <input class="form-control" type="text" name="name" placeholder="Full name with title" required /><br />
            <input class="form-control" type="text" name="email" placeholder="E-mail address" required /><br />
            <input class="form-control" type="password" name="pass" placeholder="Password" required /><br />
            <input class="btn btn-primary" type="submit" value="register" />
        </form>
    </div>
</div>
<?php } ?>