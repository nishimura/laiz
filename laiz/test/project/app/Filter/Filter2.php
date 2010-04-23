<?php

use laiz\lib\action\Result;

class Filter_Filter2
{
    public $prop2;
    public $prop3;
    public function filter(Result $res)
    {
        $this->prop2 = 'bar!';
        $this->prop3 = 'bar!';

        $res->prop3 = 'baz!';
    }
}
