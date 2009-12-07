<?php
/**
 * Simple O/R Mapper Iterator Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * Simple O/R Mapper Iterator Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Laiz_Db_Iterator
{
    public function setParams($params);
    public function setReplacements($reps);
}
