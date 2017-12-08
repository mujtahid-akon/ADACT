<?php
/**
 * @var bool   $logged_in
 * @var string $form_type
 * @var string $email
 */
?>

<div class="container-table">
    <div class="vertical-center-row text-center">
<?php if(!isset($logged_in) OR !$logged_in){ ?>
        <h1 class="title"><a href="home"><?php print \ADACT\Config::SITE_TITLE; ?></a></h1>
<?php } ?>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
<?php if($form_type == 'reset'){ ?>
            <script>
const check_pass = function(){
    if($('#pass').val() !== $('#pass_conf').val()){
        alert("Passwords do not matched!");
        return false;
    }
    return true;
}
                </script>
                <h3>Reset your password</h3>
                <form class="form-veritcal" id="reset_form" method="post" action="reset_pass" onsubmit="return check_pass()">
                    <input class="form-control" type="text" name="email" placeholder="E-mail address" value="<?php print $email ?>" disabled /><br />
                    <input class="form-control" type="password" id="pass" name="pass" placeholder="Password" required /><br/>
                    <input class="form-control" type="password" id="pass_conf" name="pass_conf" placeholder="Confirm password" required /><br/>
                    <input class="btn btn-primary" type="submit" value=" Reset password " />
                </form>
<?php }else{ ?>
                <h3>Request a password reset</h3>
                <form class="form-veritcal" id="reset_form" method="post" action="reset_pass">
                    <input class="form-control" type="text" name="email" placeholder="E-mail address" <?php if(!empty($email)) print 'value="' . $email . '"';  ?> required /><br />
                    <input class="btn btn-primary" type="submit" value=" Request " />
                </form>
<?php } ?>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
</div>