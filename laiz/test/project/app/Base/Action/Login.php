<?php

use laiz\lib\session\AutoLogin;
use laiz\lib\action\Result;

class Base_Action_Login
{
    public $name;
    public $password;
    public $auto;

    public function act(Session_Login $login, AutoLogin $auto, Result $res)
    {
        $ret = $auto->login($login, $this->name, $this->password, $this->auto);
        if (!$ret){
            $res->message = 'login failed!'; // for output
            return 'action:AutoLogin';
        }

        return 'redirect:/AutoLogin';
    }
}
