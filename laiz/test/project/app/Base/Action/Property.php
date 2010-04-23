<?php

class Base_Action_Property
{
    public $prop1;
    public $prop3;

    public function act()
    {
        $this->prop1 = 'foo!';
        $this->prop3 = 'baz!';
    }
}
