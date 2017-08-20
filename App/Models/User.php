<?php

namespace AWorDS\App\Models;

use \AWorDS\App\Constants;

class User extends Model{
    /**
     * InputMethod login
     *
     * @param string $email
     * @param string $pass
     * @param null|string $remember
     * @return int
     */
    function login($email, $pass, $remember){
       if($stmt = $this->mysqli->prepare('SELECT `user_id`, `password`, `locked` FROM users WHERE email=?')){
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == Constants::COUNT_ONE){
                $stmt->bind_result($user_id, $hash, $isLocked);
                $stmt->fetch();
                if($isLocked == Constants::BOOL_TRUE) return Constants::LOGIN_LOCKED;
                if(password_verify($pass, $hash)){
                    if(!$remember){   // Session only login
                        $this->new_session($user_id, session_id(), Constants::SESSION_SESSION);
                    }else{                  // Cookie login
                        $this->new_session($user_id, $this->activition_key(), Constants::SESSION_COOKIE);
                    }
                    return Constants::LOGIN_SUCCESS;
                }
            }
        }
        
        $this->add_to_login_attempts($email);
        return Constants::LOGIN_FAILURE;
    }
    
    function logout(){
        if($this->login_check()){
            if(isset($_SESSION['session'])){ // Top priority
                if($stmt = $this->mysqli->prepare("DELETE FROM `active_sessions` WHERE `user_id` = ? AND `session_id` = ? AND `type` = 'session'")){
                    $stmt->bind_param('is', $_SESSION['session']['id'], session_id());
                    $stmt->execute();
                    $stmt->store_result();
                    unset($_SESSION['session']);
                }
            }else{  // Cookie
                if(isset($_COOKIE['u_id'], $_COOKIE['id'])){
                    if($stmt = $this->mysqli->prepare("DELETE FROM `active_sessions` WHERE `user_id` = ? AND `session_id` = ? AND `type` = 'cookie'")){
                        $stmt->bind_param('is', $_COOKIE['u_id'], $_COOKIE['id']);
                        $stmt->execute();
                        $stmt->store_result();
                        setcookie('u_id', '', time() - Constants::SESSION_COOKIE_TIME);
                        setcookie('id', '',time() - Constants::SESSION_COOKIE_TIME);
                    }
                }
            }
        }
    }
    
    function register($name, $email, $pass){
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        if($this->user_exists($email) == Constants::ACCOUNT_EXISTS) return Constants::ACCOUNT_EXISTS;
        $activation_key = $this->activition_key();
        if($stmt = $this->mysqli->prepare('INSERT INTO `users`(`name`, `email`, `password`, `joined_date`, `locked`, `activation_key`) VALUE(?,?,?, NOW(), 1, ?)')){
            $stmt->bind_param('ssss', $name, $email, $hash, $activation_key);
            $stmt->execute();
            if($stmt->affected_rows == Constants::COUNT_ONE){
                $this->email_new_ac($name, $email, $activation_key);
                return Constants::REGISTER_SUCCESS;
            }
        }
        return Constants::REGISTER_FAILURE;
    }
    
    function unlock($email, $key){
        if($stmt = $this->mysqli->prepare('UPDATE `users` SET `locked` = 0, activation_key = null WHERE `email` = ? AND activation_key = ?')){
            $stmt->bind_param('ss', $email, $key);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == Constants::COUNT_ONE) return true;
        }
        return false;
    }
    
    function valid_reset_request($email, $key){
        return $this->unlock($email, $key);
    }
    
    function email_reset_request($email){
        $to      = $email;
        $subject = "AWorDS: Password reset request"; // TODO: use TITLE instead
        $activition_key = $this->new_activation_key($email);
        // TODO: change website name and address
        $reset_address = 'http://web-muntashir.codeanyapp.com/AWorDS/reset_pass&email=' . urlencode($email) . '&key=' . urlencode($activition_key);
        $message = <<<EOF
<!DOCTYPE html>
<html>
<head>
  <title>{$subject}</title>
</head>
<body>
  <h1>AWorDS</h1>
  <address><a href="mailto:info@awords.co.uk">info@awords.co.uk</a></address>
  <address>221/B, Baker Street, London, UK</address>
  <br/>
  <p>Dear user,</p>
  <p>You have requested for resetting your password. If this is really you, please follow the link bellow:</p>
  <p><a href="{$reset_address}" target='_blank'>{$reset_address}</a> (This is a one time link)</p>
  <p>Please disregard this email if you didn't request for the password reset.</a>
</body>
</html>
EOF;
        self::email($to, $subject, $message);
    }
    
    function reset_password($email, $pass){
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        if($stmt = $this->mysqli->prepare('UPDATE `users` SET `password` = ? WHERE `email` = ?')){
            $stmt->bind_param('ss', $hash, $email);
            $stmt->execute();
            $stmt->store_result();
        }
    }
    
    function user_exists($email){
        if($stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM users WHERE email=?')){
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            if($count == Constants::COUNT_ONE) return Constants::ACCOUNT_EXISTS;
        }
        return Constants::ACCOUNT_DOES_NOT_EXIST;
    }
    
    function email_new_ac($name, $email, $activition_key){
        $to      = "{$name} <{$email}>";
        $subject = "Welcome to AWorDS!"; // TODO: use TITILE instead
        // TODO: change website name and address
        $verification_address = 'http://web-muntashir.codeanyapp.com/AWorDS/unlock&email=' . urlencode($email) . '&key=' . urlencode($activition_key);
        $message = <<<EOF
<!DOCTYPE html>
<html>
<head>
  <title>{$subject}</title>
</head>
<body>
  <h1>AWorDS</h1>
  <address><a href="mailto:info@awords.co.uk">info@awords.co.uk</a></address>
  <address>221/B, Baker Street, London, UK</address>
  <br/>
  <p>Dear {$name},</p>
  <p>Your email was used to create a new account in <a href="http://web-muntashir.codeanyapp.com/AWorDS" target='_blank'>AWorDS</a>.
      If this was really you please verify your account by following the link below:</p>
  <p><a href="{$verification_address}" target='_blank'>{$verification_address}</a></p>
  <p>Thanks for creating account with us.</a>
</body>
</html>
EOF;
        self::email($to, $subject, $message);
    }
    
    function new_activation_key($email){
        $activation_key = $this->activition_key();
        if($stmt = $this->mysqli->prepare('UPDATE `users` SET `activation_key` = ? WHERE `email` = ?')){
            $stmt->bind_param('ss', $activation_key, $email);
            $stmt->execute();
            $stmt->store_result();
        }
        return $activation_key;
    }
    
    function get_email(){
        $user_id = isset($_SESSION['session']) ? $_SESSION['session']['id'] : $_COOKIE['u_id'];
        
        if($stmt = $this->mysqli->prepare('SELECT `email` FROM `users` WHERE `user_id` = ?')){
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == Constants::COUNT_ONE){
                $stmt->bind_result($email);
                $stmt->fetch();
                return $email;
            }
        }
        return false;
    }
    
    private function new_session($user_id, $session_id, $session_type){
        if($session_type == Constants::SESSION_SESSION){
            $_SESSION['session']['id']    = $user_id;
        }else{
            setcookie('u_id', $user_id, time() + Constants::SESSION_COOKIE_TIME);
            setcookie('id', $session_id,time() + Constants::SESSION_COOKIE_TIME);
        }
        if($stmt = $this->mysqli->prepare('INSERT INTO `active_sessions` VALUE(?,?,?)')){
            $stmt->bind_param('iss', $user_id, $session_id, $session_type);
            $stmt->execute();
            $stmt->store_result();
        }
    }
    
    private function activition_key(){
        $max = ceil(Constants::ACTIVATION_KEY_LENGTH / 40);
        $random = '';
        for ($i = 0; $i < $max; $i ++) {
        $random .= sha1(microtime(true).mt_rand(10000,90000));
        }
        $random = substr($random, 0, Constants::ACTIVATION_KEY_LENGTH);
        if($stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM users WHERE activation_key=?')){
            $stmt->bind_param('s', $random);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            if($count == Constants::COUNT_ONE) return $this->activition_key();
        }
        return $random;
    }
    
    // TODO: Not implemented yet
    private function add_to_login_attempts($email){
        if($this->user_exists($email) == Constants::ACCOUNT_EXISTS){
            // Doesn't need to check if the a/c is locked, since it's already being checked at login()
            if($stmt = $this->mysqli->prepare('SELECT `user_id` FROM `users` WHERE `email` = ?')){
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows == Constants::COUNT_ONE){
                    $stmt->bind_result($user_id);
                    $stmt->fetch();
                    if($stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM `login_attempts` WHERE `user_id` = '. $user_id)){
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($count);
                        $stmt->fetch();
                        $sql = ($count == Constants::COUNT_ONE) ?
                            'UPDATE `login_attempts` SET `attempts` = `attempts` + 1' : 'INSERT INTO `login_attempts` VALUE(' . $user_id . ', 1)';
                        // First, get the no. of attempts
                        if($count == Constants::COUNT_ONE){
                            if($stmt = $this->mysqli->prepare('SELECT `attempts` FROM `login_attempts` WHERE `user_id` = '. $user_id)){
                                $stmt->execute();
                                $stmt->store_result();
                                $stmt->bind_result($attempts);
                                $stmt->fetch();
                                if($attempts == Constants::MAX_LOGIN_ATTEMPTS){
                                    // The account is locked
                                    return Constants::LOGIN_LOCKED;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}