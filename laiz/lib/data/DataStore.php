<?php
/**
 * Data Store Interface.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\data;

/**
 * Data Store Interface.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface DataStore
{
    /**
     * @param mixed $dsn
     * @return bool
     */
    public function setDsn(Array $dsn);

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set($key, $value, $expire = null);

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key);

    /**
     * @return mixed
     */
    public function get($key);

    /**
     * @return DataStore
     */
    public function clear();
}
