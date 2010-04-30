<?php

use laiz\lib\test\ActionTest;
use laiz\lib\test\Assert;

class Base_Action_ActionTest implements ActionTest
{
    public $prop1;
    public $prop2;
    public $prop3;
    public function act()
    {
        $this->prop1 = 3;
        $a = new StdClass();
        $a->bar = 'baz';
        $this->prop3 = $a;
    }

    public function testPrep(Assert $a)
    {
        $a->equal($this->prop2, 'foo');
    }

    public function test1(Assert $a)
    {
        $a->numeric($this->prop1);
        $a->equal($this->prop1, 3, 'prop1 is 3');
    }

    public function test2(Assert $a)
    {
        $a->equal($this->prop3->bar, 'baz');
    }

    public function getActionName()
    {
        return 'ActionTest';
    }
}