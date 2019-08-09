<?php


namespace ADACT\App\Models;


interface UserPrivilegeHandlerInterface {
    const GUEST_USER_ID = 0;
    function is_guest();
}