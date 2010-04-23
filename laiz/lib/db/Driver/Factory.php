<?php
/**
 * Laiz_Db Factory Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2006-2009 Satoshi Nishimura
 */

namespace laiz\lib\db;

/**
 * Class of creation database driver class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Driver_Factory
{
    const BASE_NAME = 'laiz\lib\db\Driver';

    static private $dbs = array();
    
    static public function factory($dsn){
        // If same dsn, return same object.
        if (isset(self::$dbs[$dsn]))
            return self::$dbs[$dsn];

        list($driver, $other) = explode(':', $dsn, 2);

        $driverFile = '';
        switch ($driver){
        case 'pgsql':
            $driverFile = 'Pgsql.php';
            $driverName = 'Pgsql';
            break;
        case 'sqlite':
            $driverFile = 'Sqlite.php';
            $driverName = 'Sqlite';
            break;
        }

        if (!$driverFile)
            throw new Exception('Database Driver not found.');

        $driverFilePath = dirname(__FILE__) . '/' . '/' . $driverFile;

        if (!file_exists($driverFilePath))
            throw new Exception('Database Driver File not found.');

        $className = preg_replace('/Factory$/', '', __CLASS__) . $driverName;
        require_once $driverFilePath;
        self::$dbs[$dsn] = new $className($dsn);
        return self::$dbs[$dsn];
    }

}
