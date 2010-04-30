<?php

use laiz\lib\action\Result;

class Base_Action_Result
{
    public $oldVar1;
    public $oldVar2;
    public function act(Result $res)
    {
        $res->var = 'persistence!';
        $this->oldVar1 = $this->oldVar2 = 'not persistence';
        return 'result2';
    }
}
