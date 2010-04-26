<?php

namespace laiz\builder;

use \RecursiveFilterIterator;

class AutoIncludeFilter extends RecursiveFilterIterator
{
    public function accept()
    {
        if (preg_match('@/Fly/@', parent::current()->getPathname()))
            return false;
        if (preg_match('@/compiled@', parent::current()->getPathname())){
            return false;
        }
        return true;
    }
}
