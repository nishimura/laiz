<?php
/**
 * Class of parsing ini file.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Class of parsing ini file.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
abstract class Laiz_Parser_Ini implements Laiz_Parser
{
    public function parse($fileName)
    {
        return $this->parseIniFile($fileName, true);
    }

    abstract public function parseIniFile($fileName, $flag = false);
}
