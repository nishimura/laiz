<?php
/**
 * Data Store Using Memcache.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\data;

use \Memcache;

/**
 * Data Store Using Memcache.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class DataStore_Memcache implements DataStore
{
    private $memcache;
    private $prefix;
    public function __construct()
    {
        $this->memcache = new Memcache();
    }

    public function setDsn(Array $dsn)
    {
        $opts = array('host' => 'localhost',
                      'port' => 11211,
                      'prefix' => '');
        foreach ($opts as $key => $value)
            if (isset($dsn[$key]))
                $opts[$key] = $dsn[$key];

        $this->prefix = $opts['prefix'];
        return $this->memcache->connect($opts['host'], $opts['port']);
    }

    public function put($key, $value, $expire = null)
    {
        $this->memcache->set($this->prefix . $key, $value,
                             MEMCACHE_COMPRESSED, $expire);
        return $this;
    }

    public function get($key)
    {
        return $this->memcache->get($this->prefix . $key);
    }

    public function clear()
    {
        $this->memcache->flush();
        return $this;
    }
}
