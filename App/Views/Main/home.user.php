<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 6/11/18
 * Time: 7:15 AM
 */

/**
 * @var bool $is_guest
 */
?>
<div class="row">
    <div class="col-md-12">
        <h3 class="title">Welcome to <span style="font-family: monospace;">ADACT</span>!</h3>
        <?php if($is_guest): ?>
        <p class="text-danger">You are free to do any experiments. <strong>All your data will be cleared after 24 hours!</strong></p>
        <?php endif; ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-6 center-text">
        <a class="wc wc-img" href="./projects/new"><i class="fa fa-file" ></i></a><br />
        <a class="wc wc-btn h2" href="./projects/new">New project</a>
    </div>
    <div class="col-sm-6 center-text">
        <a class="wc wc-img" href="./projects"><i class="fa fa-list" ></i></a><br />
        <a class="wc wc-btn h2" href="./projects">All projects</a>
    </div>
</div>
<style>
    .wc {
        text-align: center;
    }
    .wc.wc-img {
        font-size: 1000%;
    }
</style>
