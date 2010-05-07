<?php

use laiz\lib\session\AutoLogin;
use laiz\lib\action\Result;

class Base_Action_Login
{
    public $name;
    public $password;
    public $auto;

    public function act(Session_Login $login, AutoLogin $auto)
    {
        if (!$login->login($this->name, $this->password)){
            $res->message = 'login failed!'; // for output
            return 'action:AutoLogin';
        }
        
        $ret = $auto->login($this->name, $this->auto);

        return 'redirect:/AutoLogin';
    }
}
