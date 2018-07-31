<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <h3 class="title">Login</h3>
        <?php if(isset($_SESSION['login_error'])): ?>
        <div class="alert alert-danger">
            <i class="fa fa-2x fa-exclamation-triangle pull-left"></i>
            <?php echo $_SESSION['login_error'] ?>
        </div>
        <?php unset($_SESSION['login_error']); endif; ?>
        <form class="form-veritcal" id="login_form" method="post" action="./login">
            <input class="form-control" type="text" name="email" placeholder="E-mail address" <?php if(!empty($email)) print 'value="' . $email . '"'; ?> required /><br />
            <input class="form-control" type="password" name="pass" placeholder="Password" required /><br/>
            <!--label><input type="checkbox" name="remember" /> Remember me for 7 days</label><br /-->
            <button class="btn btn-4 button small gray" type="submit">
                <i class="fa fa-sign-in" aria-hidden="true"></i> Login
            </button>
            <div style="margin-top: 10px">Forget password? <a href="./reset_pass">Reset</a>.</div>
        </form>
    </div>
    <div class="col-md-4"></div>
</div>
