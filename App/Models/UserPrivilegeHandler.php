<?php

namespace ADACT\App\Models;

class UserPrivilegeHandler implements UserPrivilegeHandlerInterface {
    function is_guest() {
        return ($_SESSION['user_id'] == self::GUEST_USER_ID);
    }
}