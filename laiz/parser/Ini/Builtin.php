<?php
/**
 * Class of parsing ini file using php function.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\parser;

/**
 * Class of parsing ini file using php function.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  1000
 */
class Ini_Builtin extends Ini
{
    public function parseIniFile($fileName, $flag = false)
    {
        return @parse_ini_file($fileName, $flag);
    }
}
