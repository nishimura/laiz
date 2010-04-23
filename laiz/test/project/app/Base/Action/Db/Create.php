<?php

use laiz\lib\db\Factory;

class Base_Action_Db_Create
{
    public $ret;
    public function act(Factory $factory)
    {
        $fooDao = $factory->create('foo');
        $vo = $fooDao->createVo();
        $vo->data = 'foobar!';
        if ($fooDao->save($vo))
            $this->ret = 'Success!';
        else
            $this->ret = 'Error!';
    }
}
