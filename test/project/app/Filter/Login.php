<?php

use laiz\lib\session\AutoLogin;

class Filter_Login
{
    public $logined;
    public function filter(AutoLogin $auto)
    {
        list($startNow, $isLogined, $userId) = $auto->autoLoginFilter();
        $this->logined = $isLogined;
    }
}
