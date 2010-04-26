<?php
/**
 * Class file for autoload.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\autoloader;

/**
 * Class for autoload.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class BasicLoader
{
    static public function autoload($name)
    {
        // for Fly_Flexy
        if (preg_match('/^Fly_Flexy_/', $name))
            return;

        $name = str_replace('\\', '/', $name);
        $file = str_replace('_', '/', $name) . '.php';
        include_once $file;
    }

    static public function init()
    {
        if (function_exists('__autoload'))
            spl_autoload_register('__autoload');
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    static public function walk(Array $objs)
    {
        spl_autoload_unregister(array(__CLASS__, 'autoload'));
        array_map(array(__CLASS__, '_walk'), $objs);

        // this method execute last
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    static private function _walk(Register $obj)
    {
        spl_autoload_register(array($obj, 'autoload'));
    }
}
