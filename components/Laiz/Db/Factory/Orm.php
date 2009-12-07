<?php

class Laiz_Db_Factory_Orm implements Laiz_Db_Factory
{
    private $config = array();

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function create($tableName)
    {
        if (!$this->config)
            $this->setConfig();

        try {
            $db = Laiz_Db_DriverFactory::factory($this->config['dsn']);
        }catch (PDOException $e){
            // PDO error
            trigger_error($e->getMessage(), E_USER_ERROR);
        }catch (Exception $e){
            // framework error
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
        $dao = new Laiz_Db_Orm_Pdo($db, $tableName);
        $dao->autoCreateConfig($this->config['autoConfig']);
        $dao->setTableConfigs($this->config['configFile']);
        return $dao;
    }
}
