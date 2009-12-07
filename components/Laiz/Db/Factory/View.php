<?php

class Laiz_Db_Factory_View implements Laiz_Db_Factory
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
            $db = Laiz_Db_DriverFactory::factory($this->config['dsn']);
        }catch (PDOException $e){
            // PDO error
            trigger_error($e->getMessage(), E_USER_ERROR);
        }catch (Exception $e){
            // framework error
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
        $dao = new Laiz_Db_View_Pdo($db);
        return new Laiz_Db_Iterator_View($dao, $sqlFile);
    }
}
