<?php
/**
 * Class of parsing ini file using php function.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Class of parsing ini file using php function.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Parser_Ini_Builtin extends Laiz_Parser_Ini
{
    public function parseIniFile($fileName, $flag = false)
    {
        return @parse_ini_file($fileName, $flag);
    }
}
