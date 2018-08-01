<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 3/12/18
 * Time: 2:00 PM
 */
?>
<div class="row">
    <div class="col-xs-12 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
        <h3 class="title">Register</h3>
        <?php if(isset($_SESSION['register_error'])): ?>
        <div class="alert alert-danger">
            <i class="fa fa-2x fa-exclamation-triangle pull-left"></i>
            <?php echo $_SESSION['register_error'] ?>
        </div>
        <?php unset($_SESSION['register_error']); endif; ?>
        <small class="text-danger"><em>Please fill out all the fields.</em></small>
        <form class="form-vertical" id="reg_form" method="post" action="./reg">
            <input class="form-control" name="name" placeholder="Full name with title" required /><br />
            <input class="form-control" name="email" placeholder="E-mail address" required /><br />
            <input class="form-control" type="password" name="pass" placeholder="Password" required /><br />
            <button class="btn btn-4 button small gray" type="submit">
                <i class="fa fa-user-plus" aria-hidden="true"></i> Register
            </button>
        </form>
    </div>
</div>
