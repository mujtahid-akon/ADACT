<div class="container-table">
    <div class="vertical-center-row text-center">
        <h1 class="title"><a href="home">AWorDS</a></h1>
        <?php if($alert_type == 'reset'){ ?>
        <p class="text-success">
            <strong>Success!</strong> Your password was reset successfully. Go to the <a href="login">login page</a> to login.
        </p>
        <?php }else{ ?>
        <p class="text-info">
            An email is sent to your email with password reset instructions. Please check your email.
        </p>
        <?php } ?>
    </div>
</div>