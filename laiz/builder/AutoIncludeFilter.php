<?php

namespace laiz\builder;

use \FilterIterator;

class AutoIncludeFilter extends FilterIterator
{
    public function accept()
    {
        if (preg_match('@/Fly/@', parent::current()))
            return false;
        if (preg_match('@/compiled/@', parent::current()))
            return false;

        return preg_match('/.php$/', parent::current());
    }
}
