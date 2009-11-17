<?php
/**
 * Class file for autoload.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Class for autoload.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Autoload
{
    static public function autoload($name)
    {
        $file = str_replace('_', '/', $name) . '.php';
        include_once $file;
    }

    static public function setAll()
    {
        if (function_exists('__autoload'))
            spl_autoload_register('__autoload');
        spl_autoload_register(array('Laiz_Autoload', 'autoload'));
    }
}
