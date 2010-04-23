<?php

class Base_Action_DisplayNone
{
    public function act()
    {
        // not use template
        highlight_file(__FILE__);
    }
}
