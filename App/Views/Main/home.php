<?php
/**
 * @var bool   $logged_in
 * @var string $title
 */
if(isset($logged_in) && $logged_in){
    require_once __DIR__ . '/home.user.php';
}else{
    require_once __DIR__ . '/home.welcome.php';
}
