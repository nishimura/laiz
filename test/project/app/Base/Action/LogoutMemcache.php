<?php

use laiz\lib\session\AutoLogin;
use laiz\lib\data\DataStore_Memcache;

class Base_Action_LogoutMemcache
{
    public $name;
    public $password;
    public $auto;

    public function act(AutoLogin $auto)
    {
        $ds = new DataStore_Memcache();
        $ds->setDsn(array('scope' => 'autologin'));
        $auto->setDataStore($ds);
        $auto->logout();

        return 'redirect:/AutoLoginMemcache';
    }
}
