<?php

use laiz\lib\session\AutoLogin;
use laiz\lib\action\Result;

class Filter_Login extends AutoLogin
{
    public $logined;
    public function filter(Result $res)
    {
        list($startNow, $isLogined, $userId, $data) = $this->autoLoginFilter();
        $this->logined = $isLogined;
    }
}
