<?php

use laiz\lib\session\AutoLogin;
use laiz\lib\data\DataStore_Memcache;
use laiz\lib\action\Result;

class Base_Action_LoginMemcache
{
    public $name;
    public $password;
    public $auto;

    public function act(Session_Login $login, AutoLogin $auto, Result $res)
    {
        if (!$login->login($this->name, $this->password)){
            $res->message = 'login failed!'; // for output
            return 'action:AutoLoginMemcache';
        }

        $ds = new DataStore_Memcache();
        $ds->setDsn(array('scope' => 'autologin'));
        $auto->setDataStore($ds);
        $ret = $auto->login($this->name, $this->auto);

        return 'redirect:/AutoLoginMemcache';
    }
}
