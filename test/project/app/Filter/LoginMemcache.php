<?php

use laiz\lib\session\AutoLogin;
use laiz\lib\data\DataStore_Memcache;

class Filter_LoginMemcache
{
    public $logined;
    public function filter(AutoLogin $auto)
    {
        $ds = new DataStore_Memcache();
        $ds->setDsn(array('scope' => 'autologin'));
        $auto->setDataStore($ds);
        list($startNow, $isLogined, $userId) = $auto->autoLoginFilter();
        $this->logined = $isLogined;
    }
}
