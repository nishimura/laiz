<?php
/**
 * O/R Mapper Iterator Mock Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * O/R Mapper Iterator Mock Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Db_Iterator_Mock implements Laiz_Db_Iterator, IteratorAggregate
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
