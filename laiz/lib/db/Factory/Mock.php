<?php

namespace laiz\lib\db;

use laiz\util\Inflector;

class Factory_Mock implements Factory
{
    private $orms = array();
    private $iterators = array();
    public function create($name)
    {
        if (isset($this->iterators[$name]))
            return $this->iterators[$name];

        if (!isset($this->orms[$name]) &&
            isset($this->orms[Inflector::singularize($name)])){
            $orm = $this->orms[Inflector::singularize($name)];
            $vos = $orm->getVos();
            //var_dump($name);
            $iterator = new Iterator_Mock($vos);

            $this->iterators[$name] = $iterator;
            return $this->iterators[$name];
        }

        if (!isset($this->orms[$name]))
            $this->orms[$name] = new Orm_Mock($name);
        return $this->orms[$name];
    }
}
