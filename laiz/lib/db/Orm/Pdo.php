<?php
/**
 * Simple O/R Mapper Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\lib\db;

use \PDO;

/**
 * Simple O/R Mapper Class using PDO.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Orm_Pdo implements Orm
{
    /** string default component directory */
    const COMPONENT_DIR = '../components/';

    /** @var string component directory */
    private $COMPONENT_DIR;

    /** @var Laiz_Db */
    private $dao;

    /**
     * Table information.
     *
     * <code>
     * array('pKey' => primary key,
     *       'columns' => array(class property name => database column name),
     *       'types'   => array(class property name => database column type),
     *       'tableName' => tableName)
     * </code>
     *
     * @var string[] $tables
     * @access private
     */
    private $tables = array();

    /** @var string current $tables setting file path. Easy cache. */
    static private $currentConfigFile;

    /** @var string current $tables setting. Easy cache. */
    static private $currentTables;

    /** @var string */
    private $tableName;

    /** @var string Flag of auto creation configure file. */
    private $autoCreateConfig;

    /** @var string Hash of check to update database. */
    static protected $dbHash;


    /**
     * Setting DSN.
     *
     * @param string $dsn
     * @access public
     */
    public function __construct(Driver $db, $tableName = null){
        $this->dao = $db;
        if ($tableName)
            $this->tableName = $tableName;
    }

    /**
     * Return of framework's componentDir.
     * @return string
     */
    private function getComponentDir(){
        if ($this->COMPONENT_DIR){
            $componentDir = $this->COMPONENT_DIR;
        }elseif (class_exists('Laiz_Configure', false)){
            $dirs = Laiz_Configure::get('Laiz_Container');
            $componentDir = $dirs['COMPONENTS_DIR'];
        }else{
            $componentDir = self::COMPONENT_DIR;
        }

        return $componentDir;
    }

    public function setTableName($tableName){
        $this->tableName = $tableName;
    }

    public function setComponentDir($dir){
        $this->COMPONENT_DIR = $dir;
    }

    /**
     * Close connection.
     *
     * @access public
     */
    public function __destruct(){
        $this->dao = null;
    }

    public function autoCreateConfig($var){
        $this->autoCreateConfig = $var;
    }

    /**
     * Return hash of database tables.
     *
     * @return string
     * @access private
     */
    private function getDbHash(){
        if (self::$dbHash)
            return self::$dbHash;
        
        $data = array();
        
        $tables = $this->dao->getMetaTables();
        //var_dump(get_class($this));
        if ($tables == array()){
            trigger_error('Failed to parse '.get_class($this), E_USER_WARNING);
        }
        foreach ($tables as $table){
            $data[$table]['columns'] = $this->dao->getMetaColumns($table);
            $data[$table]['pKey']    = $this->dao->getMetaPrimaryKeys($table);
        }
        $data[] = $tables;
        self::$dbHash = md5(serialize($data));
        return self::$dbHash;
    }

    /**
     * Check to update database.
     *
     * @param string $configFile
     * @return bool
     * @access private
     */
    private function isDbUpdated($configFile){
        if (!file_exists($configFile))
            return true;

        // DBが更新されているかどうかをハッシュで比較
        $lines = file($configFile, 1);
        if ($lines === false)
            return true;

        $hash = $this->getDbHash();
        $oldHash = '';
        foreach ($lines as $line){
            if (preg_match('/^;#hash:(.*)$/', $line, $matches)){
                $oldHash = $matches[1];
                break;
            }
        }
        return ($oldHash != $hash);
    }

    /**
     * return table exists.
     *
     * @param string $tableName
     * @return bool
     */
    public function existsTable($tableName = '')
    {
        if ($tableName === '')
            $tableName = $this->tableName;

        return isset($this->tables[$tableName]);
    }

    /**
     * Store tables information.
     *
     * @param string $configFile
     * @param bool $notUseCache
     * @return bool
     * @access public
     */
    public function setTableConfigs($configFile, $notUseCache = false){
        if ($notUseCache && self::$currentConfigFile == $configFile){
            // 同じデータベースを使う場合はstaticを利用したキャッシュ
            $this->tables = self::$currentTables;
            return true;
        }

        $configFilePath = $configFile;

        if ($this->autoCreateConfig){
            if ($this->isDbUpdated($configFilePath)){
                // DBが更新されている場合は設定ファイルの自動生成を試みる
                $this->createTableConfigs($configFilePath);
            }
        }

        $tables = parse_ini_file($configFilePath, TRUE);

        if (!$tables)
            return false;

        foreach ($tables as $key => $value){
            if (preg_match('/[^a-zA-Z0-9_:]/', $key, $matches)){
                trigger_error("There is a not available character $matches[0] in table section [$key].", E_USER_WARNING);
                return false;
            }                    
            if (strstr($key, ':')){
                list($tableName, $pKeyName) = explode(':', $key);

                $this->tables[$tableName]['pKey']    = $pKeyName;
            }else{
                $tableName = $key;

                // プライマリキーを推測
                if (in_array($tableName . '_id', $value))
                    $this->tables[$tableName]['pKey'] = $tableName . '_id';
                if (in_array($tableName . '_ID', $value))
                    $this->tables[$tableName]['pKey'] = $tableName . '_ID';
            }
            $columns = array();
            $types = array();
            foreach ($value as $key => $columnType){
                list($column, $type) = explode(':', $columnType);
                $columns[$key] = $column;
                $types[$key]   = $type;
            }
            $this->tables[$tableName]['columns']   = $columns;
            $this->tables[$tableName]['types']     = $types;
            $this->tables[$tableName]['tableName'] = $tableName;
        }


        self::$currentConfigFile = $configFile;
        self::$currentTables     = $this->tables;

        return true;
    }

    /**
     * Save tables information to ini file.
     *
     * @param string $fileName file name
     * @return bool
     * @access public
     */
    public function createTableConfigs($fileName){
        $tables = $this->dao->getMetaTables();

        // open ini file
        $fp = fopen($fileName, 'w', 1);
        if (!$fp){           
            trigger_error("Failed to open $fileName for write.", E_USER_WARNING);
            return false;
        }

        // parse and write
        foreach ($tables as $table){
            // read primary key
            $pKeys = $this->dao->getMetaPrimaryKeys($table);
            if (is_array($pKeys) && count($pKeys) == 1){
                $pKey = ':' . $pKeys[0];
            }else{
                $pKey = '';
            }

            // write table section
            fwrite($fp, "[$table$pKey]\n");

            // read columns information
            $columnTypes = $this->dao->getMetaColumns($table);
            $columns = array();
            $types = array();
            foreach ($columnTypes as $column){
                list($c, $t) = explode(':', $column);
                $columns[] = $c;
                $types[] = $t;
            }
            foreach ($columns as $key => $column){
                // convert column names to vo arguments
                $voColumn = str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($column))));
                $voColumn[0] = strtolower($voColumn[0]);

                // write columns information
                fwrite($fp, "$voColumn\t= $column:${types[$key]}\n");
            }

            fwrite($fp, "\n");
        }

        // write hash
        $hash = $this->getDbHash();
        fwrite($fp, ";#hash:$hash\n");
        
        fclose($fp);
        return true;
    }

    /**
     * Create VO instance and return that.
     * 
     * @param string $tableName
     * @return Object
     * @access private
     */
    private function _createVo($tableName){
        if (!isset($this->tables[$tableName])){
            if (!$this->tables)
                trigger_error("Not configured table information.", E_USER_WARNING);
            else
                trigger_error("Not exists database configuration data of table [$tableName].", E_USER_WARNING);

            return null;
        }

        $voClassSuffix = implode('', array_map('ucfirst', explode('_', $tableName)));
        $className = 'Vo_'. $voClassSuffix;

        if (!class_exists($className, false)){
            // Create VO instance
            $fileName = $this->getComponentDir() . 'lib/db/Vo/'. $voClassSuffix . '.php';
            if (file_exists($this->getComponentDir() . $fileName)){
                require_once($fileName);
                if(!class_exists($className)){
                    trigger_error("Not defined $className in $fileName.", E_USER_WARNING);
                    eval("namespace laiz\\lib\\db;\n class $className implements Vo{}");
                }
            }else{
                eval('namespace laiz\\lib\\db;' . "\n class $className implements Vo{}");
            }
        }
        $className = 'laiz\lib\db\\' . $className;
        $vo = new $className($this->tables[$tableName]);

        foreach ($this->tables[$tableName]['columns'] as $key => $value){
            $vo->$key = null;
        }

        return $vo;
    }

    /**
     * Create VO instance and initialize value and return that.
     *
     * @param string $tableName
     * @param string[] $data array(propertyName => value)
     * @return Object
     * @access public
     */
    public function createVo($data = array()){
        if (!$this->tableName){
            trigger_error('Not defined table name.', E_USER_WARNING);
            return null;
        }
        
        $obj = $this->_createVo($this->tableName);
        if (!$obj)
            return null;

        if (!is_array($data) || $data === array())
            return $obj;

        $obj =  $this->setVoData($obj, $data);

        return $obj;
    }

    /**
     * Initialize VO values by array and return that.
     *
     * @param Object $class VO instance
     * @param string[] $v data array
     * @return bool
     * @access public
     */
    public function setVoData(Laiz_Vo $vo, $data){
        if (!isset($this->tables[$this->tableName]['columns'])){
            trigger_error('Not defined table configuration.', E_USER_WARNING);
            return $vo;
        }

        foreach ($this->tables[$this->tableName]['columns'] as $key => $value){
            if (isset($data[$key])){
                $vo->$key = $data[$key];
            }else{
                $vo->$key = null;
            }
        }

        return $vo;
    }

    /**
     * Get VO and return that.
     *
     * @param string $where SQL's where
     * @param bool $onlyone
     * @return Object
     * @access public
     */
    function getVo($where = null, $onlyone = false){
        if (!isset($this->tableName)){
            trigger_error('Not defined table configuration.', E_USER_WARNING);
            return;
        }
        
        if (is_numeric($where)){
            // get vo by primary key
            if (!isset($this->tables[$this->tableName]['pKey'])){
                trigger_error('Not defined primary key in ' . $this->tableName . ' table.',
                              E_USER_WARNING);
                return;
            }
            
            $pKeyName = $this->tables[$this->tableName]['pKey'];
            $query = $this->_getSelectString(array('where' => "$pKeyName = ? "));
            $params = $where;
            
        }elseif (is_array($where)){
            $params = array();
            $query = $this->_getSelectString(array('where' => $where), $params);

        }else{
            trigger_error("Unexcertain format of argument.", E_USER_WARNING);
            return;
        }

        $stmt = $this->dao->query($query, $params);
        if (!$stmt){
            $errInfo = $stmt->errorInfo();
            trigger_error('['.$errInfo[0] .':'. $errInfo[1] .']'. $errInfo[2]);
            return null;
        }
        
        $vo = $this->createVo();
        $columnMeta = array();
        $this->bindColumns($stmt, $vo);

        $ret = $stmt->fetch(PDO::FETCH_BOUND);
        if (!$ret){
            $stmt = null;
            return null;
        }

        if ($onlyone && $count = $stmt->rowCount() != 1){
            trigger_error("Getting $count result of unique key.", E_USER_WARNING);
            $stmt = null;
            return null;
        }

        $stmt = null;
        return $vo;
    }

    private function bindColumns($stmt, $vo, $columns = null){
        if ($columns !== null)
            $columns = (array)$columns;

        foreach ($this->tables[$this->tableName]['columns'] as $key => $value){
            if ($columns !== null && !in_array($key, $columns)){
                continue;
            }

            // bindColumn cannot auto convert value's type.
            // need set value's type
            $stmt->bindColumn($value, $vo->$key, $this->tables[$this->tableName]['types'][$key]);
        }
    }

    /**
     * Return PDOStatement to prepare getting VOs.
     *
     * @param array $options
     * @return PDOStatement
     * @access public
     */
    public function getVosStatement($options, $param = array()){
        if (!isset($this->tableName)){
            trigger_error('Not defined table configuration.', E_USER_WARNING);
            return;
        }
        
        if (!isset($this->tables[$this->tableName])){
            trigger_error('Not defined ' . $this->tableName . ' table configuration.', E_USER_WARNING);
            return;
        }
        
        $query = $this->_getSelectString($options, $param);
        $stmt = $this->dao->query($query, $param);
        if (!$stmt)
            return array();
        
        return $stmt;
    }

    /**
     * Return VOs.
     *
     * @param array $options
     * @return Laiz_Db_Vo[]
     * @access public
     */
    function getVos($options = array(), $params = array()){
        $stmt = $this->getVosStatement($options, $params);

        $vo = $this->createVo();
        $this->bindColumns($stmt, $vo);

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

    /**
     * Return VOs by array.
     *
     * @param array $options
     * @return array
     * @access public
     */
    public function getVosArray($options = array()){
        $stmt = $this->getVosStatement($options);

        $this->bindColumns($stmt, $vo);

        $vos = array();
        while ($stmt->fetch(PDO::FETCH_BOUND)){
            $vo = array();
            foreach ($this->tables[$this->tableName]['columns'] as $key => $value){
                $vo[$value] = $$key;
            }
            $vos[] = $vo;
        }

        return $vos;
    }

    /**
     * Bind for iterator.
     *
     * ==check== TODO: remove 'get' from method name.
     *
     * @param PDOStatement $stmt
     * @param array columns property name of columns.
     * @access public
     */
    public function getBindArray($stmt, $vo, $columns = null){
        if (is_string($columns))
            $columns = (array)$columns;

        $this->bindColumns($stmt, $vo, $columns);
    }

    /**
     * Store VO data to database.
     *
     * @param Laiz_Db_Vo|array $vo
     * @param array $where
     * @param bool $isForceInsert force insert flag.
     * @return int|false
     * @access public
     */
    function save($vo, $where = null, $isForceInsert = false){
        if (is_array($vo)){
            $changes = 0;
            foreach ($vo as $v){
                $ret = $this->save($v);
                if ($ret === false)
                    return false;
                if (is_numeric($ret))
                    $changes += $ret;
            }
            return $changes;
        }

        if (!($vo instanceof Vo)){
            trigger_error("$vo is not Laiz_Db_Vo object.", E_USER_WARNING);
            return false;
        }

        if (!isset($this->tables[$this->tableName])){
            trigger_error('Not defined table configuration.', E_USER_WARNING);
            return false;
        }
        
        $configs = $this->tables[$this->tableName];
        $pKeyVar = array_search($configs['pKey'], $configs['columns']);


        // prepare data
        $columnNames = array();
        $params = array();
        foreach ($configs['columns'] as $propertyName => $columnName){
            if (property_exists($vo, $propertyName) && (isset($vo->$pKeyVar) || isset($where))){
                $params[] = $vo->$propertyName;
                $columnNames[] = $columnName;
            }elseif (isset($vo->$propertyName)){
                $params[] = $vo->$propertyName;
                $columnNames[] = $columnName;
            }elseif ($propertyName == 'registTime' || $propertyName == 'updateTime'){
                /*
                 * auto setting of timestamp
                 */
                $params[] = date('Y-m-d H:i:s');
                $columnNames[] = $columnName;
            }
        }

        if (count($columnNames) == 0){
            trigger_error('Not setting registration data.');
            return false;
        }

        // 
        // Set primary key or $where => update
        // or
        // data is set
        //
        // when update, null property is null column
        // but when insert, not assign.
        //
        if (!$isForceInsert && (isset($vo->$pKeyVar) || isset($where))){
            $columns = array();
            foreach ($columnNames as $columnName)
                $columns[] = " $columnName = ? ";

            if (isset($where)){
                $where = $this->getWhereString($where, $params);
            }else{
                $where = " WHERE " . $configs['pKey'] . ' = ? ';
                $params[] = $vo->$pKeyVar;
            }
            $sql = 'UPDATE ' . $this->tableName . ' SET ' . implode(', ', $columns) . " $where ";
        }else{
            // インサート文発行
            $sql = 'INSERT INTO ' . $this->tableName . ' (' . implode(', ', $columnNames) . ') '
                .  'VALUES (' .implode(', ', array_fill(0, count($columnNames), '?')) . ') ';
        }

        return $this->dao->execute($sql, $params);
    }

    /**
     * If set primary key, force insert.
     *
     * @param Laiz_Db_Vo
     * @return int|false
     */
    public function insert($vo){
        return $this->save($vo, null, true);
    }

    /**
     * Delete VO data by primary key.
     * 
     * @param int|Laiz_Db_Vo $vo
     * @return int
     * @access public
     */
    function delete($vo){
        $tableName = $this->tableName;
        if (!isset($tableName)){
            trigger_error('Not defined table configuration.', E_USER_WARNING);
            return 0;
        }

        $configs = $this->tables[$tableName];
        $pKeyVar = array_search($configs['pKey'], $configs['columns']);
        $idName = $configs['columns'][$pKeyVar];
        if (is_object($vo)){
            if (!isset($vo->$pKeyVar) || !is_numeric($vo->$pKeyVar)){
                trigger_error('Not defined primary key in ' . $tableName . ' table.', E_USER_WARNING);
                return 0;
            }
            $id = $vo->$pKeyVar;

        }else if(is_numeric($vo)){
            $id = $vo;

        }else{
            return 0;
        }
        
        $query = "DELETE FROM $tableName  WHERE $idName = ? ";

        $count = $this->dao->execute($query, $id);
        if (!$count){
            // debug
            //trigger_error("Failed to delete data: $query", E_USER_WARNING);
        }

        return $count;
    }

    /**
     * Return database column name by VO's property.
     *
     * @param string $tableName
     * @param string $propertyName
     * @return string
     * @access public
     */
    function getColumnName($propertyName, $tableName = ''){
        if (!$tableName)
            $tableName = $this->tableName;
        
        if (!isset($this->tables[$tableName]['columns'][$propertyName])){
            trigger_error("Value of $propertyName of vo variable is not exists in $tableName table.", E_USER_WARNING);
            return '';
        }

        return $this->tables[$tableName]['columns'][$propertyName];
    }

    /**
     * Return SQL where string.
     *
     * $where = " columnName1 = 'value1' OR columnName2 = 'value2'";
     * $where = array('voProperty1' => 'value1', 'voProperty2' => 'value2');
     * $where = array('voProperty' => array('like', 'searchValue'));
     *
     * @param string $tableName
     * @param mixed $whereMixed
     * @return string
     * @access private
     */
    public function getWhereString($whereMixed, &$params = array()){
        if (!is_array($whereMixed)){
            return "WHERE $whereMixed";
        }

        $where = array();
        foreach ($whereMixed as $key => $value){
            if (is_array($value) && is_string($key)){
                // like, in, syntax
                if (!isset($value[0]) || !isset($value[1])){
                    trigger_error("Value of where $key key is wrong.", E_USER_WARNING);
                    return '';
                }

                $whereKey = strtolower($value[0]); // where
                $char = '';                        // comparing syntax, >, <
                $columnName = $this->getColumnName($key); // column name
                switch ($whereKey){
                case 'like':
                case 'prefixlike':
                case 'suffixlike':
                    if (!is_string($value[1])){
                        trigger_error("Value of like $key key in where is wrong.", E_USER_WARNING);
                        break;
                    }

                    if ($whereKey == 'prefixlike')
                        $params[] = $value[1] . '%';
                    elseif ($whereKey == 'suffixlike')
                        $params[] = '%' . $value[1];
                    else
                        $params[] = '%' . $value[1] . '%';

                    $where[$key] = " $columnName like ? ";
                    
                    break;

                case 'under': // less
                    $char = '<';
                case 'less': // and less
                    if (!$char) $char = '<=';
                case 'over': // over
                    if (!$char) $char = '>';
                case 'more': // and over
                    if (!$char) $char = '>=';

                    $params[] = $value[1];
                    $where[$key] = " $columnName $char ? ";
                    break;

                case 'between':
                    if (!isset($value[2])){
                        trigger_error("Between needs two value in $key.", E_USER_WARNING);
                        break;
                    }
                    $params[] = $value[1];
                    $params[] = $value[2];
                    $where[$key] = " $columnName >= ? AND $columnName <= ? ";
                    break;

                case 'in':
                    if (!is_array($value[1])){
                        trigger_error("Not array in of $key of where.", E_USER_WARNING);
                        break;
                    }

                    $inValues = $value[1];
                    $inParam = array();
                    foreach ($inValues as $inValue){
                        $params[] = $inValue;
                        $inParam[] = '?';
                    }
                    $columnValue = implode(',', $inParam);
                    $where[$key] = " $columnName in ($columnValue) ";
                    break;

                default:
                    break;
                }
                
            }else{
                if (is_numeric($key) && is_string($value)){
                    // where by string
                    $where[$key] = $value;

                }elseif (is_numeric($key) && is_array($value) && isset($value[0], $value[1])){
                    // string([0]) + prepared statement variables
                    for ($i = 1; $i < count($value); $i++)
                        $params[] = $value[$i];
                    $where[$key] = $value[0];

                }elseif ($value === 'notnull'){
                    $columnName = $this->getColumnName($key);
                    $where[$key] = " $columnName is not null ";

                }elseif ($value === null){
                    $columnName = $this->getColumnName($key);
                    $where[$key] = " $columnName is null ";

                }else{
                    // simple key=value match
                    $columnName  = $this->getColumnName($key);

                    $params[] = $value;
                    $where[$key] = " $columnName = ? ";
                }
            }
        }
        $where = implode(' AND ', $where);
        $wherestr = " WHERE $where ";

        return ' WHERE ' . $where;
    }

    /**
     * Return SELECT string.
     *
     * <code>
     * $options = array('columns' => column name)
     * $options = array('columns' => array(column name1, column name2))
     * $options = array('where' => ??) 
     * $options = array('order' => ??) 
     * $options = array('limit' => num)
     * $options = array('offset' => offset)
     * </code>
     *
     * @param array $options other option
     * @param array $columns column names $data = array('column_name' => 'value', ...)
     * @param string $where
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return string
     * paccess private
     */
    private function _getSelectString($options = array(), &$params = array()){
        $tableName = $this->tableName;

        if (isset($options['columns'])){
            $colConf = $this->tables[$tableName]['columns'];
            
            if (is_string($options['columns'])){
                $strColumn = $this->getColumnName($options['columns']);
                
            }else if (is_array($options['columns'])){
                $strColumn = implode(', ', array_map(array($this, 'getColumnName'),
                                                     $options['columns']));
            }
        }else{
            $strColumn = '*';
        }

        // set where
        if (isset($options['where'])){
            $wherestr = $this->getWhereString($options['where'], $params);
        }else{ $wherestr = ''; }

        // set order
        $orders = array();
        if (isset($options['order'])){
            if (is_array($options['order'])){
                //$orders = array_map(array($this, 'getColumnName'), $options['order']);
                foreach ($options['order'] as $o){
                    $orders[$o] = $this->getColumnName($o);
                }

            }else{
                $orders[$options['order']] = $this->getColumnName($options['order']);
            }

            /*
             * ==check== Not implements test.
             * 20070827 confirmed action.
             */

            // abbr. format
            if (isset($options['desc']) && !isset($options['descending']))
                $options['descending'] = $options['desc'];

            if (isset($options['descending'])){
                if (!is_array($options['descending']) && isset($orders[$options['descending']])){
                    $orders[$options['descending']] .= ' DESC ';

                }elseif (is_array($options['descending'])){
                    foreach ($options['descending'] as $o){
                        if (isset($orders[$o])){
                            $orders[$o] .= ' DESC ';
                        }
                    }
                }

            }
            $order = ' ORDER BY ' . implode(',', $orders);

        }else{
            $order = '';
        }

        // set limit, offset
        if (isset($options['limit']) && is_numeric($options['limit']) && $options['limit'] > 0)
            $limit = " LIMIT $options[limit] ";
        else
            $limit = '';
        if (isset($options['offset']) && is_numeric($options['offset']) && $options['offset'] > 0)
            $offset = " OFFSET $options[offset] ";
        else
            $offset = '';
        
        $query = 'SELECT '
            . $strColumn
            . " FROM $tableName $wherestr $order $limit $offset";

        return $query;
    }

    public function getMaxId(){
        if (!isset($this->tableName)){
            trigger_error('Not defined table configuration.', E_USER_WARNING);
            return;
        }

        if (!isset($this->tables[$this->tableName]['pKey'])){
            trigger_error('Not defined primary key in ' . $this->tableName . " table.",
                          E_USER_WARNING);
            return;
        }

        $pKeyName = $this->tables[$this->tableName]['pKey'];
        $tableName = $this->tableName;
        $query = "SELECT MAX($pKeyName) FROM $tableName;";
        $stmt = $this->dao->query($query);
        if (!$stmt)
            return 0;

        $ret = $stmt->fetch(PDO::FETCH_NUM);
        if (!$ret){
            $stmt = null;
            return 0;
        }

        $stmt = null;
        return $ret[0];
    }

    public function count($where = ''){
        if (!isset($this->tableName)){
            trigger_error('Not defined table configuration.', E_USER_WARNING);
            return;
        }

        $tableName = $this->tableName;
        $params = array();
        if ($where)
            $where = $this->getWhereString($where, $params);
        else
            $where = '';
        $query = "SELECT count(*) from $tableName " . $where;

        $stmt = $this->dao->query($query, $params);
        if (!$stmt){
            $errInfo = $stmt->errorInfo();
            trigger_error('['.$errInfo[0] .':'. $errInfo[1] .']'. $errInfo[2]);
            return null;
        }
        
        $ret = $stmt->fetch(PDO::FETCH_NUM);
        $stmt = null;
        if (!$ret){
            return null;
        }
        return $ret[0];
    }

    /**
     * Return column type information.
     *
     * @param string $columnName
     */
    public function getColumnType($columnName){
        if (!isset($this->tableName)){
            trigger_error('Not defined table configuration.', E_USER_WARNING);
            return;
        }

        if (!isset($this->tables[$this->tableName]['types'][$columnName])){
            trigger_error('Not defined type information.', E_USER_WARNING);
            return;
        }

        return $this->tables[$this->tableName]['types'][$columnName];
    }

    /**
     * ==check== postgresql's currval
     */
    public function currval(){
        if (!isset($this->tableName)){
            trigger_error('Not defined table configuration.', E_USER_WARNING);
            return;
        }

        if (!isset($this->tables[$this->tableName]['pKey'])){
            trigger_error('Not defined primary key in ' . $this->tableName . " table.",
                          E_USER_WARNING);
            return;
        }

        return $this->dao->currval($this->tableName, $this->tables[$this->tableName]['pKey']);
    }

    /**
     * Low level query.
     *
     * @param string $query
     * @param mixed $prepare
     * @return PDOStatement
     * @access public
     */
    function query($query, $prepare = null){
        return $this->dao->query($query, $prepare);
    }

    /**
     * Begin transaction.
     * 
     * @access public
     */
    function begin(){
        $this->dao->begin();
    }

    /**
     * Commit transaction.
     * 
     * @access public
     */
    function commit(){
        $this->dao->commit();
    }

    /**
     * Abort transaction.
     * 
     * @access public
     */
    function abort(){
        $this->dao->abort();
    }

    /**
     * Lock table
     * 
     * @param string $table
     * @access public
     */
    function lock($table = ''){
        if (!$table)
            $table = $this->tableName;
            
        $this->dao->lock($table);
    }

    /**
     * Quote string for query.
     *
     * @param string $str
     * @param int $type PDO::PARAM_STRなど
     */
    public function quote($str, $type = null){
        return $this->dao->quote($str, $type);
    }

    //========================================================================================
    //                                     experimental
    //========================================================================================

    /**
     * Set PDO error mode.
     *
     * @param int $mode
     */
    public function setErrorMode($mode){
        $this->dao->setErrorMode($mode);
    }
}
