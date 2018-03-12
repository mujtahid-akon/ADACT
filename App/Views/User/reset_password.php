<?php
/**
 * @var bool   $logged_in
 * @var string $alert_type
 */
?>
<div class="row" style="margin-top: 20px">
    <div class="col-md-12">
<?php if($alert_type == 'reset'){ ?>
        <p class="text-success">
            <strong>Success!</strong> Your password was reset successfully.<?php if(!$logged_in){ ?> Go to the <a href="login">login page</a> to login.<?php } ?>
        </p>
<?php }else{ ?>
        <p class="text-info">
            An email is sent to your email address with password reset instructions. Please check your email.
        </p>
<?php } ?>
    </div>
</div>
