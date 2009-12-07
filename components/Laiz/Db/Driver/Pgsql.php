<?php
/**
 * DB Driver Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 */

/**
 * PostgreSQL Driver Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Db_Driver_Pgsql extends Laiz_Db_Driver{
    const META_TABLES_SQL = "select tablename,'T' from pg_tables where tablename not like 'pg\_%'
        and tablename not in ('sql_features', 'sql_implementation_info', 'sql_languages',
         'sql_packages', 'sql_sizing', 'sql_sizing_profiles')

        union
        select viewname,'V' from pg_views where schemaname = 'public'";

    const META_COLUMNS_SQL = "SELECT a.attname, t.typname, a.attlen, a.atttypmod, a.attnotnull, a.atthasdef, a.attnum
FROM pg_class c, pg_attribute a, pg_type t, pg_namespace n
WHERE relkind in ('r','v') AND (c.relname=? or c.relname = lower(?))
 and c.relnamespace=n.oid and n.nspname='public'
        AND a.attnum > 0
        AND a.atttypid = t.oid AND a.attrelid = c.oid ORDER BY a.attnum";

    const META_KEYS_SQL = 'select a.attname from pg_attribute a, pg_constraint c, pg_class r
                           where c.conrelid = r.oid and a.attrelid = r.oid and a.attnum = any(c.conkey)
                             and r.relname = ? and c.contype = \'p\'';

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
        $stmt = $this->query(self::META_COLUMNS_SQL, array($tableName, $tableName));
        if ($stmt === false){
            return array();
        }

        $rows = array();
        foreach ($stmt as $row){
            switch($row[1]){
            case 'float8':
                $type = PDO::PARAM_STR; // Not exists PARAM_FLOAT by current version.
                break;

            case 'int4':
            case 'int8':
            case 'numeric':
                $type = PDO::PARAM_INT;
                break;

            default:
                $type = PDO::PARAM_STR;
                break;
            }

            $rows[] = $row[0] . ':' . $type;
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
        $stmt = $this->query(self::META_KEYS_SQL, $tableName);
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
     * Lock table.
     *
     * @param string $table
     * @access public
     */
    public function lock($table){
        $this->query("LOCK TABLE $table IN SHARE ROW EXCLUSIVE MODE");
    }

    /**
     * Return current sequence.
     *
     * @param string $table
     */
    public function currval($table, $pkey){
        $stmt = $this->query("SELECT currval('{$table}_{$pkey}_seq')");

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
