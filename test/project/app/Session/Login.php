<?php

use laiz\lib\session\Login;

class Session_Login
{
    public function login($name, $password)
    {
        if ($name === 'foo' && $password === 'bar')
            return true;
        return false;
    }
}
