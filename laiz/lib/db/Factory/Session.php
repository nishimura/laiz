<?php

namespace laiz\lib\db;

use \laiz\lib\session\Session;

class Factory_Session implements Factory
{
    private $s;
    private $orms = array();
    public function __construct(Session $s)
    {
        $this->s = $s;
    }

    public function create($name)
    {
        if (!isset($this->orms[$name]))
            $this->orms[$name] = new Orm_Session($this->s, $name);
        return $this->orms[$name];
    }
}
