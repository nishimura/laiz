<?php

namespace laiz\lib\db;

use \PDOException;

class Factory_View implements Factory
{
    private $config = array();

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Return iterator.
     *
     * @param string $sqlFile
     * @return Laiz_Db_Iterator_View
     */
    public function create($sqlFile)
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
        $dao = new View_Pdo($db);
        return new Iterator_View($dao, $sqlFile);
    }
}
