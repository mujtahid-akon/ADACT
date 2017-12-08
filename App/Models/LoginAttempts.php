<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 11/18/17
 * Time: 8:25 AM
 */

namespace ADACT\App\Models;


/**
 * @property  string name
 */
class LoginAttempts extends Model{
    const USER  = 1;
    const EMAIL = 2;
    const MAX_LOGIN_ATTEMPTS = 3;

    const LOCK_ACCOUNT    = 1;
    const INCREMENT_BY_1  = 2;
    const ADD_TO_ATTEMPTS = 3;

    private $_user_id = null;
    private $_email = null;
    /**
     * LoginAttempts constructor.
     * @param string|int $unique_id Either user ID or email ID
     * @param int        $type Which type of ID is provided
     */
    function __construct($unique_id, $type = self::USER){
        parent::__construct();
        if($type == self::EMAIL){
            $this->_email = $unique_id;
        }else $this->_user_id = $unique_id;
    }

    public function add(){
        switch ($this->_check_attempts()){
            case self::LOCK_ACCOUNT:
                $this->_lock();
                break;
            case self::INCREMENT_BY_1:
                $this->_increment();
                break;
            case self::ADD_TO_ATTEMPTS:
                $this->_add();
        }
    }

    public function delete(){
        if(@$stmt = $this->mysqli->prepare('DELETE FROM login_attempts WHERE user_id = ?')){
            $stmt->bind_param('i', $this->_user_id);
            $stmt->execute();
            $stmt->store_result();
        }
    }

    private function _check_attempts(){
        if($stmt = $this->mysqli->prepare('SELECT u.name, u.user_id, a.attempts, COUNT(a.attempts) AS attempt_exists FROM users AS u LEFT OUTER JOIN login_attempts AS a ON a.user_id = u.user_id WHERE email = \'' . $this->_email . '\' GROUP BY attempts')){
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 1){
                $stmt->bind_result($this->name, $this->_user_id, $attempts, $attempts_exists);
                $stmt->fetch();
                if($attempts_exists){
                    if($attempts == self::MAX_LOGIN_ATTEMPTS) return self::LOCK_ACCOUNT;
                    else return self::INCREMENT_BY_1;
                }else return self::ADD_TO_ATTEMPTS;
            }
        }
        return false;
    }

    private function _increment(){
        if($stmt = $this->mysqli->prepare('UPDATE login_attempts SET attempts = attempts + 1 WHERE user_id = ?')){
            $stmt->bind_param('i', $this->_user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1) return true;
        }
        return false;
    }

    private function _add(){
        if($stmt = $this->mysqli->prepare('INSERT INTO login_attempts VALUE(?, 1)')){
            $stmt->bind_param('i', $this->_user_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->affected_rows == 1) return true;
        }
        return false;
    }

    private function _lock(){
        $activation_key = (new User())->activation_key();
        if($stmt = $this->mysqli->prepare('UPDATE users SET locked = TRUE, activation_key = ? WHERE user_id = ?')){
            $stmt->bind_param('si', $activation_key, $this->_user_id);
            $stmt->execute();
            $stmt->store_result();
        }
        $this->delete();
        $this->_email_unlock_key($this->name, $this->_email, $activation_key);
    }

    private function _email_unlock_key($name, $email, $activation_key){ // TODO: use http://ipinfo.com to get ip related data
        $subject = self::SITE_TITLE . ': Unlock your account';
        $verification_address = self::WEB_ADDRESS . '/unlock' . URL_SEPARATOR . 'email=' . urlencode($email) . '&key=' . urlencode($activation_key);
        $body = <<< EOF
<p>Someone tried to access you account in an illegal manner which is why we've locked your account.
Please, use the link below to unlock your account:</p>
<p><a href="{$verification_address}" target='_blank'>{$verification_address}</a></p>
<p>This is just a procedure to secure your account. This doesn't mean that your account is compromised anyhow.</p>
EOF;
        return self::formatted_email($name, $email, $subject, $body);
    }
}