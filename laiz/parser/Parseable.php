<?php

namespace laiz\parser;

use \laiz\builder\Aggregatable;

/**
 * @laiz\Component
 */
interface Parseable extends Aggregatable
{
    /**
     * @param string $fineName
     * @return string
     */
    public function parse($fileName);
}
