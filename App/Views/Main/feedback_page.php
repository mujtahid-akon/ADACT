<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 11/18/17
 * Time: 11:19 PM
 */

if(!isset($logged_in) OR !$logged_in){ ?>
<div class="navbar">
    <div class="navbar-inner nav">
        <div class="navbar-header">
            <a href="/" class="navbar-brand title"><?php print \ADACT\Config::SITE_TITLE; ?></a>
        </div>
    </div>
</div>
<?php } ?>
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <h3>Feedback</h3>
        <?php
        if(isset($_SESSION['register_error'])){
            print "<div class=\"alert alert-danger\">{$_SESSION['register_error']}</div>";
            unset($_SESSION['register_error']);
        }
        ?>
        <small class="text-danger"><em>Please fill out all the boxes.</em></small>
        <form class="form-vertical" id="reg_form" method="post" action="/reg">
            <input class="form-control" name="name" placeholder="Full name with title" required /><br />
            <input class="form-control" name="email" placeholder="E-mail address" required /><br />
            <input class="form-control" name="subject" placeholder="Subject" required /><br />
            <textarea class="form-control" name="feedback" placeholder="Your feedback" required></textarea><br />
            <input class="btn btn-primary" type="submit" value="Submit" />
        </form>
    </div>
    <div class="col-md-3"></div>
</div>
