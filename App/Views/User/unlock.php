<?php
if($is_unlocked){
    $message = '<p class="text-success"><strong>Your account is successfully unlocked!</strong>
    Now use the <a href="login&email=' . urlencode($email) . '">login page</a> to login to your account. Thank you.</p>';
}else{
    $message = '<p class="text-danger"><strong>Account unlock failed!</strong> Your account either is not created or have already unlocked.</p>';
}
?>
<div class="container-table">
    <div class="vertical-center-row text-center">
        <h1 class="title"><a href="home">AWorDS</a></h1>
        <?php print $message ?>
    </div>
</div>