<?php

use laiz\lib\session\AutoLogin;
use laiz\lib\data\DataStore_Memcache;

class Filter_LoginMemcache
{
    public $logined;
    public $loginMessage;
    public function filter(AutoLogin $auto)
    {
        $ds = new DataStore_Memcache();
        $ds->setDsn(array('scope' => 'autologin'));
        $auto->setDataStore($ds);

        $startNow = $auto->autoLoginStart();
        $this->logined = $auto->isLogined();
        $this->loginMessage = $auto->getUserId() . ' is logined with memcache!';

        if ($startNow){
            // Initialize application session values
        }
    }
}
