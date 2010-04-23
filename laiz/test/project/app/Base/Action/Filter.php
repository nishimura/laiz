<?php

use laiz\lib\action\Result;

class Base_Action_Filter
{
    public $prop3;
    public function act(Result $res)
    {
        $this->prop3 = $res->prop3;
    }
}
