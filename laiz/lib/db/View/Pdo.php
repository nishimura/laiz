<?php
/**
 * Simple Database View Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\db;

use \PDOStatement;

/**
 * Simple Database View Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class View_Pdo implements View
{
    protected $db;

    public function __construct(Driver $db)
    {
        $this->db = $db;
    }

    /**
     * Execute query and return pdo statement.
     *
     * @param string $sqlFile
     * @param array $params parameter of prepared statement.
     * @param mixed $replace arguments of sprintf for replacement sql string.
     * @return PDOStatement
     */
    public function prepareStmt($sqlFile, $params = null, $replace = null)
    {
        $file = str_replace('_', '/', $sqlFile) . '.sql';

        $sql = file_get_contents($file, true);
        if ($replace !== null){
            $replace = (array)$replace;
            array_unshift($replace, $sql);
            $sql = call_user_func_array('sprintf', $replace);
            if (!$sql){
                trigger_error('sprintf');
                return false;
            }
        }

        if ($params !== null)
            $stmt = $this->db->query($sql, $params);
        else
            $stmt = $this->db->query($sql);
        if (!$stmt){
            trigger_error('query error! sql="' . $sql . '"', E_USER_WARNING);
            return false;
        }

        if ($stmt->errorCode() != '00000'){
            $errInfo = $stmt->errorInfo();
            trigger_error('['.$errInfo[0].':'.$errInfo[1].']'.$errInfo[2], E_USER_WARNING);
            return false;
        }

        return $stmt;
    }

    public function createVo($sqlFile)
    {
        $sqlFile = str_replace('/', '_', $sqlFile);
        $className = 'Vo_' . implode('', array_map('ucfirst', explode('_', $sqlFile)));

        if (!class_exists('laiz\\lib\\db\\' . $className, false))
            eval("namespace laiz\\lib\\db;\n class $className implements Vo{}");

        $className = 'laiz\lib\db\\' . $className;
        return new $className();
    }

    public function bind(PDOStatement $stmt, Vo $vo)
    {
        $columnCount = $stmt->columnCount();
        $columnTypes = array();
        $columnNames = array();
        $VoNames     = array();
        for ($i = 0; $i < $columnCount; $i++){
            $meta = $stmt->getColumnMeta($i);
            if (!$meta)
                break;
            //if (isset($meta['pdo_type']))
                $columnTypes[$i] = $meta['pdo_type'];
            $columnNames[$i] = $meta['name'];
            $name = implode('', array_map('ucfirst', explode('_', $meta['name'])));
            $name[0] = strtolower($name[0]);
            $voNames[$i] = $name;
        }


        for($i = 0; $i < $columnCount; $i++){
            $name = $voNames[$i];
            $vo->$name = null;
            $stmt->bindColumn($columnNames[$i], $vo->$name, $columnTypes[$i]);
        }
    }

    /**
     * Return VO by sql file, parameter, replacement string.
     *
     * @param string $sqlFile
     * @param array $params params of prepared statement
     * @param string|array $replace replacement string
     * @return Laiz_Db_Vo
     */
    public function getVo($sqlFile, $params = null, $replace = null)
    {
        $stmt = $this->prepareStmt($sqlFile, $params, $replace);
        if (!$stmt)
            return false;

        $vo = $this->createVo($sqlFile);

        $columnCount = $stmt->columnCount();
        $columnData  = $stmt->fetch(PDO::FETCH_NUM);
        for ($i = 0; $i < $columnCount; $i++){
            $meta = $stmt->getColumnMeta($i);
            if (!$meta)
                break;
            $name = implode('', array_map('ucfirst', explode('_', $meta['name'])));
            $name[0] = strtolower($name[0]);
            $vo->$name = $columnData[$i];
        }

        return $vo;
    }

    /**
     * Return VOs by sql file, parameter, replacement string.
     *
     * @param string $sqlFile
     * @param array $params params of prepared statement
     * @param string|array $replace replacement string
     * @return Laiz_Db_Vo
     */
    public function getVos($sqlFile, $params = array(), $replace = null){
        $stmt = $this->prepareStmt($sqlFile, $params, $replace);
        if (!$stmt)
            return false;

        $vo = $this->createVo($sqlFile);

        $this->bind($stmt, $vo);

        $vos = array();
        while ($stmt->fetch(PDO::FETCH_BOUND)){
            /*
             * same instance by clone.
             * foreach is double speed.
             * serialize is fast.
             */
            $vos[] = unserialize(serialize($vo));
        }

        return $vos;
    }
}
