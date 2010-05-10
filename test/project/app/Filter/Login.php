<?php

use laiz\lib\session\AutoLogin;

class Filter_Login
{
    public $logined;
    public $loginMessage;
    public function filter(AutoLogin $auto)
    {
        $startNow = $auto->autoLoginStart();
        $this->logined = $auto->isLogined();
        $this->loginMessage = $auto->getUserId() . ' is logined!';

        if ($startNow){
            // Initialize application session values
        }
    }
}
