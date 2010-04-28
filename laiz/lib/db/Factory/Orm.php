<?php

namespace laiz\lib\db;

use laiz\util\Inflector;

class Factory_Orm implements Factory
{
    private $config = array();

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function create($tableName)
    {
        if (!$this->config){
            trigger_error('Not configured', E_USER_WARNING);
            return;
        }

        try {
            $db = Driver_Factory::factory($this->config['dsn']);
        }catch (PDOException $e){
            // PDO error
            trigger_error($e->getMessage(), E_USER_ERROR);
        }catch (Exception $e){
            // framework error
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
        $dao = new Orm_Pdo($db, $tableName);
        $dao->autoCreateConfig($this->config['autoConfig']);
        $dao->setTableConfigs($this->config['configFile']);
        if (!$dao->existsTable()
            && $dao->existsTable(Inflector::singularize($tableName))){
            $dao->setTableName(Inflector::singularize($tableName));
            $dao = new Iterator_Orm($dao);
        }
        return $dao;
    }
}
