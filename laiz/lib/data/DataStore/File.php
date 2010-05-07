<?php
/**
 * Data Store Using Files.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\data;

/**
 * Data Store Using Files.
 *
 * Using file name as key.
 * The character string that cannot be used as file names
 * cannot be used as keys.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class DataStore_File implements DataStore
{
    // ==TODO==
    public function __construct()
    {
        
    }

    public function setDsn(Array $dsn)
    {

    }

    public function set($key, $vlaue, $expire = null)
    {

    }

    public function delete($key)
    {
    }

    public function get($key)
    {

    }

    public function clear()
    {

    }
}
