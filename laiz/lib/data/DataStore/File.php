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

use \laiz\lib\util\Configure;

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
class DataStore_File1 implements DataStore
{
    private $scope = 'laiz_datastore_file';

    public function setDsn(Array $dsn)
    {
        if (!isset($dsn['scope']))
            return false;
        $this->scope = $dsn['scope'];
        return true;
    }

    public function set($key, $value, $expire = null)
    {
        $file = $this->getFilePath($key);
        return file_put_contents($file, serialize($value));
    }

    public function delete($key)
    {
        $file = $this->getFilePath($key);
        return unlink($file);
    }

    public function get($key)
    {
        $file = $this->getFilePath($key);
        if (!file_exists($file))
            return null;

        return unserialize(file_get_contents($file));
    }

    public function clear()
    {
        // TODO
    }

    private function getFilePath($key)
    {
        $cacheDir = Configure::getCacheDir()
            . 'laiz_datastore_file/'
            . $this->scope;
        if (!is_dir($cacheDir))
            mkdir($cacheDir, 0777, true); // mkdir -p

        $file = $cacheDir . '/'
            . $key . '.serialized';
        return $file;
    }
}
