<?php

use laiz\lib\db\Factory;

class Base_Action_Db_Iterator
{
    public $iterator;
    public function act(Factory $factory)
    {
        $this->iterator = $factory->create('foos');
    }
}
