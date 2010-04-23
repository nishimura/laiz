<?php

namespace laiz\lib\db;

use \PDOException;

class Factory_Transaction implements Factory
{
    private $config = array();

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function create($tableName)
    {
        try {
            $db = Driver_Factory::factory($this->config['dsn']);
        }catch (PDOException $e){
            // PDO error
            trigger_error($e->getMessage(), E_USER_ERROR);
        }catch (Exception $e){
            // framework error
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
        return $db;
    }
}
