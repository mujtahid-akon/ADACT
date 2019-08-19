<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/20/2017
 * Time: 10:11 PM
 */

namespace ADACT\App\Controllers;

use ADACT\App\Models\Emailer;
use ADACT\Config;

class Main extends Controller
{
    /**
     * Homepage controller
     *
     * Generates the homepage
     */
    public function home(){
        /** @var \ADACT\App\Models\User $user */
        $user = $this->set_model('User');
        if($user->user != null && $user->user['is_guest']){
            $this->redirect('projects/new');
            exit();
        }
        $logged_in = $user->login_check();
        $this->set('title', Config::SITE_TITLE);
        $this->set('logged_in', $logged_in);
        $this->set('is_guest', $user->user != null ? $user->user['is_guest'] : null);
        $this->set('active_tab', 'home');
        if(!$logged_in) $this->set('standalone', true);
    }

    /**
     * Feedback page controller
     *
     * Generates the feedback page
     */
    public function feedback_page(){
        /** @var \ADACT\App\Models\User $user */
        $user = $this->set_model('User');
        $logged_in = $user->login_check();
        $this->set('logged_in', $logged_in);
        $this->set('is_guest', $user->user != null ? $user->user['is_guest'] : null);
    }

    /**
     * Feedback controller
     *
     * Processes the feedback
     */
    public function feedback(){
        /**
         * @var string $name
         * @var string $email
         * @var string $subject
         * @var string $feedback
         */
        extract($this->get_params());
        if(empty($name) OR empty($email) OR empty($subject) OR empty($feedback)){
            $_SESSION['feedback_error'] = 'You need to fill out all the fields!';
            $_SESSION['feedback_info']  = $this->get_params();
        }else{
            $mailer = new Emailer();
            $mailer->setAddress(Config::MAIL_FROM, Config::MAIL_NAME);
            $mailer->setFrom($email, $name);
            $mailer->setSubject($subject);
            $mailer->setMessage($feedback, false);
            if($mailer->send()){
                $_SESSION['feedback_success'] = 'Your feedback is sent successfully.';
            }else{
                $_SESSION['feedback_error'] = 'Failed to send your feedback! Please try again.';
                $_SESSION['feedback_info']  = $this->get_params();
            }
        }
        $this->redirect('feedback');
    }

    /**
     * About page controller
     *
     * Generates the about page
     */
    public function about(){
        /** @var \ADACT\App\Models\User $user */
        $user = $this->set_model('User');
        $logged_in = $user->login_check();
        $this->set('title', Config::SITE_TITLE);
        $this->set('logged_in', $logged_in);
        $this->set('is_guest', $user->user != null ? $user->user['is_guest'] : null);
    }
}
