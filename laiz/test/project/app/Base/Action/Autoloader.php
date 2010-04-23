<?php

class Base_Action_Autoloader
{
    public $name;
    public function act(FooClass $class)
    {
        $this->name = get_class($class);
    }
}
