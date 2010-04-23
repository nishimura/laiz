<?php
/**
 * O/R Mapper Iterator Mock Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\db;

use \IteratorAggregate;
use \ArrayObject;

/**
 * O/R Mapper Iterator Mock Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Iterator_Mock implements Iterator, IteratorAggregate
{
    private $iterator;
    public function __construct($vos = null)
    {
        if (is_array($vos))
            $this->setIterator($vos);
    }

    public function setIterator(Array $vos)
    {
        $this->iterator = new ArrayObject($vos);
        return $this;
    }

    public function getIterator()
    {
        return $this->iterator;
    }

    public function setParams($params)
    {
    }

    public function setReplacements($reps)
    {
    }
}
