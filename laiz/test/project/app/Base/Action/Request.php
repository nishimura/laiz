<?php

class Base_Action_Request
{
    public $req;
    public function act()
    {
        $this->req .= ' is requested!';
    }
}
