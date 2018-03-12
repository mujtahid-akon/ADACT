<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 3/12/18
 * Time: 2:00 PM
 */
?>
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <h3>Register</h3>
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
            <input class="form-control" type="password" name="pass" placeholder="Password" required /><br />
            <input class="btn btn-primary" type="submit" value="register" />
        </form>
    </div>
    <div class="col-md-3"></div>
</div>
