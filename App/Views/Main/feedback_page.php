<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 11/18/17
 * Time: 11:19 PM
 */
?>
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <h3>Feedback</h3>
        <?php
        if(isset($_SESSION['feedback_error'])){
            print "<div class=\"alert alert-danger\">{$_SESSION['feedback_error']}</div>";
            unset($_SESSION['feedback_error']);
        }
        if(isset($_SESSION['feedback_success'])){
            print "<div class=\"alert alert-success\">{$_SESSION['feedback_success']}</div>";
            unset($_SESSION['feedback_success']);
        }
        ?>
        <small class="text-danger"><em>Please fill out all the fields.</em></small>
        <form class="form-vertical" id="feedback_form" method="post" action="./feedback">
            <input class="form-control" name="name" placeholder="Full name with title" value="<?php print (!isset($_SESSION['feedback_info']) ? '' : $_SESSION['feedback_info']['name']) ?>"  /><br />
            <input class="form-control" name="email" placeholder="E-mail address" value="<?php print (!isset($_SESSION['feedback_info']) ? '' : $_SESSION['feedback_info']['email']) ?>"  /><br />
            <input class="form-control" name="subject" placeholder="Subject" value="<?php print (!isset($_SESSION['feedback_info']) ? '' : $_SESSION['feedback_info']['subject']) ?>"  /><br />
            <textarea class="form-control" name="feedback" placeholder="Your feedback" style="height: 300px;" ><?php print (!isset($_SESSION['feedback_info']) ? '' : $_SESSION['feedback_info']['feedback']) ?></textarea><br />
            <input class="btn btn-primary" type="submit" value="Submit" />
        </form>
        <?php if(isset($_SESSION['feedback_info'])) unset($_SESSION['feedback_info']); ?>
    </div>
    <div class="col-md-3"></div>
</div>
