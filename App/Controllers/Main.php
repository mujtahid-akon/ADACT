<?php
/**
 * Created by PhpStorm.
 * User: Hp User
 * Date: 4/20/2017
 * Time: 10:11 PM
 */

namespace AWorDS\App\Controllers;

use AWorDS\Config;

class Main extends Controller
{
    public function home(){
        $this->set_model('User');

        /**
         * @var \AWorDS\App\Models\User $user
         */
        $user = $this->{$this->_model};
        $logged_in = $user->login_check();
        $this->set('title', Config::SITE_TITLE);
        $this->set('logged_in', $logged_in);
        if(!$logged_in) $this->_template->hide_header(); // FIXME
        else{
            $this->set('active_tab', 'home');
        }
    }
}
