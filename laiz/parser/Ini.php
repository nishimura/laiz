<?php
/**
 * Class of parsing ini file.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\parser;

/**
 * Class of parsing ini file.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
abstract class Ini implements Parseable
{
    public function parse($fileName)
    {
        return $this->parseIniFile($fileName, true);
    }

    abstract public function parseIniFile($fileName, $flag = false);
}
