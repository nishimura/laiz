<?php
/**
 * Custom Session Class with DataStore.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\session;

use \laiz\lib\data\DataStore;

/**
 * Custom Session Class with DataStore.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Session_Custom implements Session
{
    private $isStarted = false;
    private $data = array();
    private $name = 'SID';
    private $sid;
    private $lifetime = 0;
    private $path = '/';
    private $domain;

    public function setSessionName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setSid($sid)
    {
        $this->sid = $sid;
        return $this;
    }

    public function setLifeTime($time)
    {
        $this->lifetime = $time;
        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    public function setDataStore(DataStore $ds)
    {
        $this->dataStore = $ds;
        return $this;
    }

    public function getDataStore()
    {
        if ($this->dataStore)
            return $this->dataStore;

        trigger_error('DataStore is not set. Call setDataStore.', E_USER_ERROR);
    }

    public function start()
    {
        if ($this->isStarted)
            return;

        if (!$this->sid && isset($_COOKIE[$this->name]))
            $this->sid = $_COOKIE[$this->name];

        if ($this->sid){
            $ds = $this->getDataStore();
            $this->data = $ds->get($this->sid);
        }else{
            $this->generateSid();
        }

        $this->isStarted = true;
    }

    private function generateSid()
    {
        $sid = hash('sha256', microtime() . mt_rand());
        setcookie($this->name, $sid, $this->lifetime, $this->path, $this->domain);
        $this->sid = $sid;
    }

    public function add($key, $value)
    {
        $this->start();
        $this->data[$key] = $value;
        return $this;
    }

    public function get($key)
    {
        $this->start();
        if (isset($this->data[$key]))
            return $this->data[$key];
        else
            return null;
    }

    public function remove($key)
    {
        $this->start();
        unset($this->data[$key]);
    }

    public function end()
    {
        $ds = $this->getDataStore();
        $ds->delete($this->sid);
        $this->deleteCookie();
        $this->data = array();
        $this->isStarted = false;
    }

    public function getSid()
    {
        return $this->sid;
    }

    public function getSessionName()
    {
        return $this->name;
    }

    private function deleteCookie()
    {
        setcookie($this->name, $this->sid, time() - 3600, $this->path, $this->domain);
    }

    public function changeSessionId()
    {
        $this->start();
        $this->deleteCookie();
        $this->generateSid();
    }

    public function __destruct()
    {
        $ds = $this->getDataStore();
        $ds->set($this->sid, $this->data, $this->lifetime);
    }
}
