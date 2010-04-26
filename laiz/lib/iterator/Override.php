<?php
/**
 * Iterator Utility Class.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\iterator;

use \IteratorIterator;

/**
 * Iterator Utility Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Override extends IteratorIterator
{
    private $callbacks = array();
    public function __construct($iterator, $callbacks = array())
    {
        parent::__construct($iterator);
        $this->callbacks = $callbacks;
    }

    public function current()
    {
        $arg = parent::current();
        if (isset($this->callbacks['current'])
            && is_callable($this->callbacks['current']))
            return call_user_func($this->callbacks['current'], $arg);
        else
            return $arg;
    }
}
