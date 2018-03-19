<?php
/**
 * @var bool   $is_unlocked
 * @var string $email
 */
if($is_unlocked){
    $message = '<p class="text-success"><strong>Your account is successfully unlocked!</strong>
    Now use the <a href="./login' . URL_SEPARATOR . 'email=' . urlencode($email) . '">login page</a> to login to your account. Thank you.</p>';
}else{
    $message = '<p class="text-danger"><strong>Account unlock failed!</strong> Your account either is not created or have already been unlocked.</p>';
}
?>
<div class="row">
    <div class="col-md-12">
        <?php print $message ?>
    </div>
</div>

