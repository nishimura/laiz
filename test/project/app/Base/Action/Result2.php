<?php

use laiz\lib\action\Result;

class Base_Action_Result2
{
    public $var;
    public $oldVar1;
    public function act(Result $res)
    {
        $this->var = $res->var;
    }
}
