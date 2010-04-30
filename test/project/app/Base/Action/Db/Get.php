<?php

use laiz\lib\db\Factory;

class Base_Action_Db_Get
{
    public $foo;

    public function act(Factory $factory)
    {
        $fooDao = $factory->create('foo');
        $this->foo = $fooDao->getVo(1);
    }
}
