<?php
/**
 * Data Store Mock Class.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\data;

/**
 * Data Store Mock Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class DataStore_Mock implements DataStore
{
    private $data = array();
    private $scope = 'default';
    public function __construct()
    {
        
    }

    public function setDsn(Array $dsn)
    {
        if (!isset($dsn['scope']))
            return false;

        $this->scope = $dsn['scope'];
        return true;
    }

    /**
     * unuse expire
     */
    public function set($key, $vlaue, $expire = null)
    {
        $this->data[$this->scope][$key] = $value;
        return true;
    }

    public function delete($key)
    {
        if (isset($this->data[$this->scope][$key]))
            unset($this->data[$this->scope][$key]);
        return true;
    }

    public function get($key)
    {
        if (isset($this->data[$this->scope][$key]))
            return $this->data[$this->scope][$key];
        else
            return null;
    }

    public function clear()
    {
        $this->data = array();
    }
}
