<?php
/**
 * Data Store Using Pdo.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\data;

use \laiz\core\Configure;
use \laiz\lib\db\Factory_Orm;

/**
 * Data Store Using Pdo.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class DataStore_Sqlite implements DataStore
{
    const TABLE_NAME = 'datastore';
    private $orm;

    public function setDsn(Array $dsn)
    {
        if (!isset($dsn['scope'])){
            trigger_error('scope option is not set.');
            return false;
        }

        $configs = Configure::get('base');
        $basePath = $configs['CACHE_DIR'] . $dsn['scope'];

        $ormConfig = array('dsn' => 'sqlite:' . $basePath . '.sq3',
                           'autoConfig' => true,
                           'configFile' => $basePath . '.ini');
        $factory = new Factory_Orm($ormConfig);
        $orm = $factory->create(self::TABLE_NAME);

        if (!$orm->existsTable()){
            // create table and configs
            $query = 'create table ' . self::TABLE_NAME . '(datastore_id primary key, value, expire)';
            $orm->query($query);
            $orm->createTableConfigs($ormConfig['configFile']);
            $orm->setTableConfigs($ormConfig['configFile'], true); // clear cache
        }

        $this->orm = $orm;

        // ==TODO== clear cache of over expire
        return true;
    }

    public function set($key, $value, $expire = null)
    {
        $vo = $this->orm->getVo($key);
        $new = false;
        if (!$vo){
            $new = true;
            $vo = $this->orm->createVo();
            $vo->datastoreId = $key;
        }

        $vo->value = serialize($value);
        $vo->expire = $expire;
        if ($new)
            $ret = $this->orm->insert($vo);
        else
            $ret = $this->orm->save($vo);
        return $ret;
    }

    public function get($key)
    {
        $vo = $this->orm->getVo($key);
        if (!$vo)
            return null;

        //==TODO== check expire
        return unserialize($vo->value);
    }

    public function delete($key)
    {
        $vo = $this->orm->getVo($key);
        return $this->orm->delete($vo);
    }

    public function clear()
    {
        $query = 'delete from ' . self::TABLE_NAME;
        $this->orm->query($query);

        return $this;
    }
}
