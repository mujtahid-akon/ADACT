<?php

namespace ADACT\App\Models;

use ADACT\Config;

class User extends Model{
    const ACTIVATION_KEY_LENGTH = 16;   // Can be up to 50

    /**
     * Login related constants
     */
    const LOGIN_LOCKED  = 2;
    const LOGIN_SUCCESS = 0;
    const LOGIN_FAILURE = 1;
    const SHORTAGE_OF_ARGUMENTS = 100;

    const MAX_LOGIN_ATTEMPTS = 3;

    /**
     * Register related constants
     */
    const REGISTER_SUCCESS = 0;
    const REGISTER_FAILURE = 1;

    /**
     * Accounts related constants
     */
    CONST ACCOUNT_EXISTS = 4;
    const ACCOUNT_DOES_NOT_EXIST = 5;

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
            if($stmt->num_rows == 1){
                $stmt->bind_result($user_id, $hash, $isLocked);
                $stmt->fetch();
                if($isLocked == 1) return self::LOGIN_LOCKED;
                if(password_verify($pass, $hash)){
                    $this->new_session($user_id, session_id());
                    return self::LOGIN_SUCCESS;
                }
            }
        }
        (new LoginAttempts($email, LoginAttempts::EMAIL))->add();
        return self::LOGIN_FAILURE;
    }

    /**
     * Log out of the current session
     *
     * @return void
     */
    function logout(){
        session_destroy(); // This can essentially do everything
    }

    /**
     * Register a new user
     *
     * @param string $name
     * @param string $email
     * @param string $pass
     * @return int ACCOUNT_EXISTS | REGISTER_SUCCESS | REGISTER_FAILURE
     */
    function register($name, $email, $pass){
        // Does the account already exist?
        if($this->user_exists($email) == self::ACCOUNT_EXISTS) return self::ACCOUNT_EXISTS;
        // Nah, go on
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $activation_key = $this->activation_key();
        if(@$stmt = $this->mysqli->prepare('INSERT INTO `users`(`name`, `email`, `password`, `joined_date`, `locked`, `activation_key`) VALUE(?,?,?, NOW(), 1, ?)')){
            $stmt->bind_param('ssss', $name, $email, $hash, $activation_key);
            $stmt->execute();
            if($stmt->affected_rows == 1) $this->email_new_ac($name, $email, $activation_key);
        }
        return self::REGISTER_FAILURE;
    }
    
    function unlock($email, $key){
        if(@$stmt = $this->mysqli->prepare('UPDATE users SET locked = 0, activation_key = \'\' WHERE email = ? AND activation_key = ?')){
            $stmt->bind_param('ss', $email, $key);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1) return true;
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
        $reset_btn = Emailer::button('Reset password', $reset_address);
        $body      = <<< EOF
<p>You have requested for resetting your password.</p>
<div>{$reset_btn}</div>
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
            if($count == 1) return self::ACCOUNT_EXISTS;
        }
        return self::ACCOUNT_DOES_NOT_EXIST;
    }
    
    function email_new_ac($name, $email, $activation_key){
        $site_title = self::SITE_TITLE;
        $website = self::WEB_ADDRESS;
        $subject = 'Welcome to ' . $site_title . '!';
        $verification_address = self::WEB_ADDRESS . '/unlock' . URL_SEPARATOR . 'email=' . urlencode($email) . '&key=' . urlencode($activation_key);
        $conf_btn = Emailer::button('Verify account', $verification_address);
        $body = <<< EOF
<p>Your email was used to create a new account in <a href="{$website}" target='_blank'>{$site_title}</a>.</p>
<div>{$conf_btn}</div>
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
        $user_id = $_SESSION['user_id'];
        if(@$stmt = $this->mysqli->prepare('SELECT `email` FROM `users` WHERE `user_id` = ?')){
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $stmt->bind_result($email);
                $stmt->fetch();
                return $email;
            }
        }
        return false;
    }

    /** Private functions */

    /**
     * new_session method.
     *
     * @param int    $user_id
     * @param string $session_id
     */
    private function new_session($user_id, $session_id){
        if(Config::DEBUG_MODE) error_log("user: $user_id, session: $session_id");
        // Set the user ID of the current user as the session data to quickly access it
        $_SESSION['user_id'] = $user_id;
        if(@$stmt = $this->mysqli->prepare('UPDATE active_sessions SET user_id = ? WHERE session_id = ?')){
            $stmt->bind_param('is', $user_id, $session_id);
            $stmt->execute();
            $stmt->store_result();
        }
        (new LoginAttempts($user_id))->delete();
    }

    /**
     * Generate unique activation key
     *
     * @return bool|string
     */
    public function activation_key(){
        $max = ceil(self::ACTIVATION_KEY_LENGTH / 40);
        $random = '';
        for($i = 0; $i < $max; $i ++) $random .= sha1(microtime(true).mt_rand(10000,90000));
        $random = substr($random, 0, self::ACTIVATION_KEY_LENGTH);
        if(@$stmt = $this->mysqli->prepare('SELECT COUNT(*) FROM users WHERE activation_key = ?')){
            $stmt->bind_param('s', $random);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($count);
            $stmt->fetch();
            if($count == 1) return $this->activation_key();
        }
        return $random;
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
        // TODO
    }

    public function get_info($user_id = null){
        if($user_id == null) $user_id = $_SESSION['user_id'];
        if(@$stmt = $this->mysqli->prepare('SELECT name, email, joined_date, locked FROM users WHERE user_id = ?')){
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $info = [];
                $stmt->bind_result($info['name'], $info['email'], $info['joined_date'], $info['locked']);
                $stmt->fetch();
                return $info;
            }
        }
        return false;
    }
}