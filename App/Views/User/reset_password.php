<?php
/**
 * @var bool   $logged_in
 * @var string $alert_type
 */
?>
<div class="container-table">
    <div class="vertical-center-row text-center">
<?php if(!$logged_in){ ?>
        <h1 class="title"><a href="home"><?php print \ADACT\Config::SITE_TITLE; ?></a></h1>
<?php }
if($alert_type == 'reset'){ ?>
        <p class="text-success">
            <strong>Success!</strong> Your password was reset successfully.<?php if(!$logged_in){ ?> Go to the <a href="login">login page</a> to login.<?php } ?>
        </p>
<?php }else{ ?>
        <p class="text-info">
            An email is sent to your email with password reset instructions. Please check your email.
        </p>
<?php } ?>
    </div>
</div>
