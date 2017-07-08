<div class="container-table">
    <div class="vertical-center-row text-center">
        <h1 class="title"><a href="home">AWorDS</a></h1>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <h3>Login</h3>
                <?php 
                if(isset($_SESSION['login_error'])){
                    print "<div class=\"alert alert-danger\">{$_SESSION['login_error']}</div>";
                    unset($_SESSION['login_error']);
                }
                ?>
                <form class="form-veritcal" id="login_form" method="post" action="login">
                    <input class="form-control" type="text" name="email" placeholder="E-mail address" <?php if(!empty($email)) print 'value="' . $email . '"'; ?> required /><br />
                    <input class="form-control" type="password" name="pass" placeholder="Password" required /><br/>
                    <label><input type="checkbox" name="remember" /> Remember me for 7 days</label><br />
                    <input class="btn btn-primary" type="submit" value=" login " />
                </form>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
</div>