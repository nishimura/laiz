<?php

use laiz\lib\session\AutoLogin;

class Base_Action_Logout
{
    public $name;
    public $password;
    public $auto;

    public function act(AutoLogin $auto)
    {
        $auto->logout();

        return 'redirect:/AutoLogin';
    }
}
