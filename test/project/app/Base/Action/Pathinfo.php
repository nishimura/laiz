<?php

class Base_Action_Pathinfo
{
    public $foo;
    public $bar;
    public $message;
    public function act()
    {
        $this->message = "[foo:$this->foo] and [bar:$this->bar] received!";
    }
}
