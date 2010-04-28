<?php

namespace laiz\lib\db;

interface Factory
{
    /**
     * Returns Database Utility Object.
     *
     * @param string $name
     * @return mixed Iterator or Orm or Driver.
     * @see Factory_Selector
     */
    public function create($name);
}
