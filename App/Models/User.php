<?php

namespace AWorDS\App\Models;

use \AWorDS\App\Constants;
use AWorDS\App\Session;

class User extends Model{
    /**
     * InputMethod login
     *
     * @param string $email
     * @param string $pass
     * @return int
     */
    function login($email, $pass){
       if(@$stmt = $this->mysqli->prepare('SELECT `user_id`, `password`, `locked` FROM users WHERE email=?')){
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == Constants::COUNT_ONE){
                $stmt->bind_result($user_id, $hash, $isLocked);
                $stmt->fetch();
                if($isLocked == Constants::BOOL_TRUE) return Constants::LOGIN_LOCKED;
                if(password_verify($pass, $hash)){
                    $this->new_session($user_id, session_id());
                    return Constants::LOGIN_SUCCESS;
                }
            }
        }
        
        $this->add_to_login_attempts($email);
        return Constants::LOGIN_FAILURE;
    }
    
    function logout(){
        session_destroy(); // This can essentially do everything
    }
    
    function register($name, $email, $pass){
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        if($this->user_exists($email) == Constants::ACCOUNT_EXISTS) return Constants::ACCOUNT_EXISTS;
        $activation_key = $this->activation_key();
        if(@$stmt = $this->mysqli->prepare('INSERT INTO `users`(`name`, `email`, `password`, `joined_date`, `locked`, `activation_key`) VALUE(?,?,?, NOW(), 1, ?)')){
            $stmt->bind_param('ssss', $name, $email, $hash, $activation_key);
            $stmt->execute();
            if($stmt->affected_rows == Constants::COUNT_ONE){
                if($this->email_new_ac($name, $email, $activation_key)){
                    return Constants::REGISTER_SUCCESS;
                }else{
                    // TODO: delete a/c too
                    return Constants::REGISTER_FAILURE;
                }
            }
        }
        return Constants::REGISTER_FAILURE;
    }
    
    function unlock($email, $key){
        if(@$stmt = $this->mysqli->prepare('UPDATE `users` SET `locked` = 0, `activation_key` = \'\' WHERE `email` = ? AND activation_key = ?')){
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
        $subject = self::SITE_TITLE . ': Password reset request';
        $activation_key = $this->new_activation_key($email);
        $reset_address = self::WEB_ADDRESS . '/reset_pass' . URL_SEPARATOR . 'email=' . urlencode($email) . '&key=' . urlencode($activation_key);
        $body    = <<< EOF
<p>You have requested for resetting your password. If this is really you, please follow the link bellow:</p>
<p><a href="{$reset_address}" target='_blank'>{$reset_address}</a> (This is a one time link)</p>
<p>Please disregard this email if you didn't request for the password reset.</a>
EOF;
        return self::formatted_email('User', $email, $subject, $body);
    }
    
    function reset_password($email, $pass){
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        if(@$stmt = $this->mysqli->prepare('UPDATE `users` SET `password` = ? WHERE `email` = ?')){
            $stmt->bind_param('ss', $hash, $email);
            $stmt->execute();
            $stmt->store_result();
        }
    }
    
    function user_exists($email){
        if(@$stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM users WHERE email=?')){
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            if($count == Constants::COUNT_ONE) return Constants::ACCOUNT_EXISTS;
        }
        return Constants::ACCOUNT_DOES_NOT_EXIST;
    }
    
    function email_new_ac($name, $email, $activation_key){
        $website = self::WEB_ADDRESS;
        $subject = 'Welcome to ' . self::SITE_TITLE . '!';
        $verification_address = self::WEB_ADDRESS . '/unlock' . URL_SEPARATOR . 'email=' . urlencode($email) . '&key=' . urlencode($activation_key);
        $body = <<< EOF
<p>Your email was used to create a new account in <a href="{$website}" target='_blank'>AWorDS</a>.
      If this was really you please verify your account by following the link below:</p>
<p><a href="{$verification_address}" target='_blank'>{$verification_address}</a></p>
<p>Thanks for creating account with us.</p>
EOF;
        return self::formatted_email($name, $email, $subject, $body);
    }
    
    function new_activation_key($email){
        $activation_key = $this->activation_key();
        if(@$stmt = $this->mysqli->prepare('UPDATE `users` SET `activation_key` = ? WHERE `email` = ?')){
            $stmt->bind_param('ss', $activation_key, $email);
            $stmt->execute();
            $stmt->store_result();
        }
        return $activation_key;
    }
    
    function get_email(){
        $user_id = isset($_SESSION['session']) ? $_SESSION['session']['id'] : $_COOKIE['u_id'];
        if(@$stmt = $this->mysqli->prepare('SELECT `email` FROM `users` WHERE `user_id` = ?')){
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
    
    private function new_session($user_id, $session_id){
        error_log("user: $user_id, session: $session_id");
        $_SESSION['session']['id'] = $user_id;

        if(@$stmt = $this->mysqli->prepare('UPDATE `active_sessions` SET user_id = ? WHERE session_id = ?')){
            $stmt->bind_param('is', $user_id, $session_id);
            error_log(var_dump($stmt->execute()));
            $stmt->store_result();
        }

    }
    
    private function activation_key(){
        $max = ceil(Constants::ACTIVATION_KEY_LENGTH / 40);
        $random = '';
        for ($i = 0; $i < $max; $i ++) {
        $random .= sha1(microtime(true).mt_rand(10000,90000));
        }
        $random = substr($random, 0, Constants::ACTIVATION_KEY_LENGTH);
        if(@$stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM users WHERE activation_key=?')){
            $stmt->bind_param('s', $random);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            if($count == Constants::COUNT_ONE) return $this->activation_key();
        }
        return $random;
    }
    
    // TODO: Not implemented yet
    private function add_to_login_attempts($email){
        if($this->user_exists($email) == Constants::ACCOUNT_EXISTS){
            // Doesn't need to check if the a/c is locked, since it's already being checked at login()
            if(@$stmt = $this->mysqli->prepare('SELECT `user_id` FROM `users` WHERE `email` = ?')){
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows == Constants::COUNT_ONE){
                    $stmt->bind_result($user_id);
                    $stmt->fetch();
                    if(@$stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM `login_attempts` WHERE `user_id` = '. $user_id)){
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

    /**
     * delete_account method
     *
     * - Delete user a/c
     * - Delete projects (including pending projects, last_projects)
     * - Delete active_sessions
     * - Delete login_attempts
     */
    function delete_account(){
        //
    }

    public function get_info($user_id = null){
        if($user_id == null) $user_id = $_SESSION['user_id'];
        if(@$stmt = $this->mysqli->prepare('SELECT name, email, joined_date, locked FROM users WHERE user_id = ?')){
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == Constants::COUNT_ONE){
                $info = [];
                $stmt->bind_result($info['name'], $info['email'], $info['joined_date'], $info['locked']);
                $stmt->fetch();
                return $info;
            }
        }
        return false;
    }
}