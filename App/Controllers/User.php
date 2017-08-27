<?php

namespace AWorDS\App\Controllers;

use \AWorDS\App\Constants;

class User extends Controller
{
    public function register(){
        $this->_redirect = true;
        $this->set_model();
        extract($this->_params);
        if(empty($name) OR empty($email) OR empty($pass)) $status = Constants::SHORTAGE_OF_ARGUMENTS;
        else $status = $this->{$this->_model}->register($name, $email, $pass);
        switch($status){
            case Constants::ACCOUNT_EXISTS:
                $_SESSION['register_error'] = "<strong>Account already exists!</strong> There's already an account associated with this account. If this email is really yours, <a href=\"reset_pass\">reset your password</a>.";
                break;
            case Constants::REGISTER_FAILURE:
                $_SESSION['register_error'] = "<strong>Failed!</strong> Account creation failed due to technical difficulties, please try again.";
                break;
            case Constants::SHORTAGE_OF_ARGUMENTS:
                $_SESSION['register_error'] = "<strong>Failed!</strong> You need to provide your name, email and password (ie. fill up all the boxes) to register.";
        }
        if($status == Constants::LOGIN_SUCCESS){
            $_SESSION['register_success'] = true;
            $this->_redirect_location = 'register_success';
        }
    }
    
    public function register_success(){
        if(!isset($_SESSION['register_success'])){
            $this->_redirect = true;
        }
        unset($_SESSION['register_success']);
        // else load the GUI
    }
    
    public function login(){
        $this->_redirect = true;
        $this->set_model();
        extract($this->_params);
        // First check the parameters
        if(empty($email) OR empty($pass)) $status = Constants::SHORTAGE_OF_ARGUMENTS;
        else $status = $this->{$this->_model}->login($email, $pass, $remember);
        switch($status){
            case Constants::LOGIN_LOCKED:
                $_SESSION['login_error'] = "<strong>Your account is locked!</strong> An email was sent to your account, use that email to unlock your account.";
                break;
            case Constants::LOGIN_FAILURE:
                $_SESSION['login_error'] = "<strong>Login failed!</strong> Please try again with your email and password or create an account if you don't have one.";
                break;
            case Constants::SHORTAGE_OF_ARGUMENTS:
                $_SESSION['login_error'] = "<strong>Login failed!</strong> You need to provide both email and password to login.";
        }
        if($status != Constants::LOGIN_SUCCESS) $this->_redirect_location = 'login';
    }
    
    public function login_page(){
        extract($this->_params);
        $this->set('email', isset($email) ? $email : '');
    }
    
    public function logout(){
        $this->set_model();
        $this->{$this->_model}->logout();
        $this->_redirect = true;
    }
    
    public function unlock(){
        $this->set_model();
        extract($this->_params);
        /**
         * @var string $email
         * @var string $key
         */
        $this->set('is_unlocked', $this->{$this->_model}->unlock($email, $key));
        $this->set('email', $email);
    }
    
    public function reset_password(){
        extract($this->_params);
        $this->set_model();
        if(empty($pass)){    // If only email is provided, send an activition code to the email
            $this->{$this->_model}->email_reset_request($email);
            $this->set('alert_type', 'request');
        }else{              // If email and password are provided, save password, provide a notification and redirect to the login page.
            if(isset($_SESSION['valid_reset_request'])){
                $this->{$this->_model}->reset_password($_SESSION['reset_email'], $pass);
                $this->set('alert_type', 'reset');
            }else{
                $this->_redirect = true;
                $this->_redirect_location = 'reset_pass';
            }
        }
    }
    
    public function reset_password_page(){
        extract($this->_params);
        /**
         * @var string $email
         * @var string $key
         */
        $this->set_model();
        $logged_in = $this->{$this->_model}->login_check();
        if(empty($key) AND !$logged_in){ // load the password reset request form
            $form_type = 'request';
        }else{           // load the password reset form if the reset request is valid
            if($this->{$this->_model}->valid_reset_request($email, $key) OR $logged_in){
                if($logged_in) $email = $this->{$this->_model}->get_email();
                $_SESSION['valid_reset_request'] = true;
                $_SESSION['reset_email'] = $email;
                $form_type = 'reset';
            }else{
                $form_type = 'request';
            }
        }
        $this->set('form_type', $form_type);
        $this->set('email', $email);
    }
}