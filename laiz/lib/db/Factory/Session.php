<?php

namespace laiz\lib\db;

use \laiz\autoloader\Register;
use \laiz\lib\session\Session;

class Factory_Session implements Factory, Register
{
    private $s;
    private $orms = array();
    public function __construct(Session $s)
    {
        $this->s = $s;
    }

    /**
     * Ini setting is needed to start session using object.
     * For example, Laiz_Db_Factory_Session = 10 in components.ini.
     */
    public function autoload($name)
    {
        if (preg_match('/^Laiz_Db_Vo_/', $name)){
            $createName = str_replace('Laiz_Db_Vo_', '', $name);
            $createName[0] = strtolower($createName[0]);
            $dao = $this->create($createName);
            $dao->createVo($name);
        }
    }

    public function create($name)
    {
        if (!isset($this->orms[$name]))
            $this->orms[$name] = new Orm_Session($this->s, $name);
        return $this->orms[$name];
    }
}
