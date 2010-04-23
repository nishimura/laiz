<?php

use laiz\autoloader\Register;

class Loader_Obj implements Register
{
    public function autoload($name)
    {
        if ($name == 'FooClass')
            include_once 'Loader/FooClass.php';
    }
}
