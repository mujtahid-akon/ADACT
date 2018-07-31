<?php
/**
 * @var bool   $logged_in
 * @var string $form_type
 * @var string $email
 */
?>
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
            <h3 class="title">Reset your password</h3>
            <form class="form-veritcal" id="reset_form" method="post" action="./reset_pass" onsubmit="return check_pass()">
                <input class="form-control" type="text" name="email" placeholder="E-mail address" value="<?php print $email ?>" readonly /><br />
                <input class="form-control" type="password" id="pass" name="pass" placeholder="Password" required /><br/>
                <input class="form-control" type="password" id="pass_conf" name="pass_conf" placeholder="Confirm password" required /><br/>
                <button class="btn btn-4 button small gray" type="submit">
                    <i class="fa fa-cog" aria-hidden="true"></i> Reset password
                </button>
            </form>
        <?php }else{ ?>
            <h3 class="title">Request a password reset</h3>
            <form class="form-veritcal" id="reset_form" method="post" action="./reset_pass">
                <input class="form-control" type="text" name="email" placeholder="E-mail address" <?php if(!empty($email)) print 'value="' . $email . '"';  ?> required /><br />
                <button class="btn btn-4 button small gray" type="submit">
                    <i class="fa fa-share" aria-hidden="true"></i> Request
                </button>
            </form>
        <?php } ?>
    </div>
    <div class="col-md-4"></div>
</div>
