<?php
class Laiz_Db_Factory_Mock implements Laiz_Db_Factory
{
    private $orms = array();
    public function create($name)
    {
        if (!isset($this->orms[$name]))
            $this->orms[$name] = new Laiz_Db_Orm_Mock($name);
        return $this->orms[$name];
    }
}
