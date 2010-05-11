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
        // default options
        $opts = array('host' => 'localhost',
                      'port' => 11211,
                      'scope' => '');

        // set user options
        foreach ($opts as $key => $value)
            if (isset($dsn[$key]))
                $opts[$key] = $dsn[$key];

        if (!$opts['scope'])
            $opts['scope'] = 'default';

        $this->prefix = $opts['scope'] . '.';
        return $this->memcache->connect($opts['host'], $opts['port']);
    }

    public function set($key, $value, $expire = null)
    {
        if (is_bool($value) || is_numeric($value)){
            // get method returns error with PHP 5.3.2 following message:
            // PHP Notice:  MemcachePool::get(): Failed to uncompress data.
            $value = (string)$value;
        }
        return $this->memcache->set($this->prefix . $key, $value,
                                    MEMCACHE_COMPRESSED, $expire);
    }

    public function delete($key)
    {
        return $this->memcache->delete($key);
    }

    public function get($key)
    {
        return $this->memcache->get($this->prefix . $key, MEMCACHE_COMPRESSED);
    }

    public function clear()
    {
        // This method clear all data on the memcache server.
        // $this->memcache->flush();
        return $this;
    }
}
