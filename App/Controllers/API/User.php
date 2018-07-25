<?php
namespace ADACT\App\Controllers\API;

use ADACT\App\HttpStatusCode;

class User extends APIController{

    public function login(){
        extract($this->get_params());
        /**
         * @var string $email
         * @var string $pass
         */
        /**
         * @var \ADACT\App\Models\User $user
         */
        $user = $this->set_model('User');
        // Go home if already logged in
        if($user->login_check()){
            $this->status(HttpStatusCode::FORBIDDEN, 'Already logged in.');
            exit();
        }
        // First check the parameters
        if(empty($email) OR empty($pass)) $status = $user::SHORTAGE_OF_ARGUMENTS;
        else $status = $this->{$this->_model}->login($email, $pass);
        switch($status){
            case $user::LOGIN_LOCKED:
                $this->status(HttpStatusCode::FORBIDDEN, 'Account is locked.');
                break;
            case $user::LOGIN_FAILURE:
                $this->status(HttpStatusCode::UNAUTHORIZED, 'Invalid email or password.');
                break;
            case $user::SHORTAGE_OF_ARGUMENTS:
                $this->status(HttpStatusCode::UNAUTHORIZED, 'email or password or both fields are empty.');
                break;
            case $user::LOGIN_SUCCESS:
                $this->status(HttpStatusCode::OK, 'Login success.');
        }
    }

    public function logout(){
        /**
         * @var \ADACT\App\Models\User $user
         */
        $user = $this->set_model('User');
        // Go home if already logged in
        if($user->login_check()){
            $user->logout();
            $this->status(HttpStatusCode::OK, 'Logout success.');
            exit();
        }else{
            $this->status(HttpStatusCode::FORBIDDEN, 'Not logged in.');
        }
    }
}