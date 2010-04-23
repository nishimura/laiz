<?php

namespace laiz\lib\db;

class Factory_Mock implements Factory
{
    private $orms = array();
    public function create($name)
    {
        if (!isset($this->orms[$name]))
            $this->orms[$name] = new Orm_Mock($name);
        return $this->orms[$name];
    }
}
