<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/20/2017
 * Time: 10:11 PM
 */

namespace ADACT\App\Controllers;

use ADACT\Config;

class Main extends Controller
{
    public function home(){
        /** @var \ADACT\App\Models\User $user */
        $user = $this->set_model('User');
        $logged_in = $user->login_check();
        $this->set('title', Config::SITE_TITLE);
        $this->set('logged_in', $logged_in);
        if(!$logged_in){
            $this->_template->hide_header(); // FIXME
        }else{
            $this->set('active_tab', 'home');
        }
    }

    public function feedback_page(){
        /** @var \ADACT\App\Models\User $user */
        $user = $this->set_model('User');
        $logged_in = $user->login_check();
        if(!$logged_in){
            $this->_template->hide_header(); // FIXME
        }else{
            $this->set('logged_in', $logged_in);
        }
    }
}
