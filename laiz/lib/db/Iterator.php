<?php
/**
 * Simple O/R Mapper Iterator Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\db;

/**
 * Simple O/R Mapper Iterator Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Iterator
{
    public function setParams($params);
}
