<?php
/**
 * Sqlite Driver Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\db;

use \PDO;

/**
 * Sqlite Driver Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Driver_Sqlite extends Driver
{
    const META_TABLES_SQL = "select name from sqlite_master";

    // 0:cid, 1:name, 2:type, 3:notnull, 4:defaultValue, 5:primaryKey
    const META_COLUMNS_SQL = "pragma table_info(%s)";

    /**
     * Return tables information.
     *
     * @return array
     * @access public
     */
    public function getMetaTables(){
        $stmt = $this->query(self::META_TABLES_SQL);
        if ($stmt === false){
            return array();
        }

        $rows = array();
        foreach ($stmt as $row){
            $rows[] = $row[0];
        }

        return $rows;
    }

    /**
     * Return columns information.
     *
     * @return array
     * @access public
     */
    public function getMetaColumns($tableName){
        $stmt = $this->query(sprintf(self::META_COLUMNS_SQL, $tableName));
        if ($stmt === false){
            trigger_error('cannot get column meta informations.', E_USER_WARNING);
            return array();
        }

        $rows = array();
        foreach ($stmt as $row){
            switch(strtolower($row[2])){
            case 'int':
            case 'integer':
                $type = PDO::PARAM_INT;
                break;

            default:
                $type = PDO::PARAM_STR;
                break;
            }

            $rows[] = $row[1] . ':' . $type;
        }

        return $rows;
    }

    /**
     * Return primary key information.
     *
     * @return array
     * @access public
     */
    public function getMetaPrimaryKeys($tableName){
        $stmt = $this->query(sprintf(self::META_COLUMNS_SQL, $tableName));
        if ($stmt === false){
            trigger_error('cannot get column meta informations.', E_USER_WARNING);
            return array();
        }

        $rows = array();
        foreach ($stmt as $row){
            if ($row[5])
                $rows[] = $row[1];
        }

        return $rows;
    }

    /**
     * Lock table.
     *
     * @param string $table
     * @access public
     */
    public function lock($table){
        // not have table level lock
        //$this->query('BEGIN EXCLUSIVE');
        trigger_error('Not have table level lock in sqlite driver', E_USER_WARNING);
    }

    /**
     * Return current sequence.
     */
    public function currval($table, $pkey){
        // not specify table
        $stmt = $this->query("SELECT last_insert_rowid()");

        if (!$stmt)
            return false;

        $ret = $stmt->fetch(PDO::FETCH_NUM);
        if (!$ret){
            $stmt = null;
            return false;
        }

        $stmt = null;
        return $ret[0];
    }
}
