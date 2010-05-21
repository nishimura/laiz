<?php
/**
 * Default Converter.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\action;

/**
 * Default Converter.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Converter_Simple implements Converter
{
    public function trim($arg)
    {
        return trim($arg);
    }

    public function removeHyphen($arg)
    {
        return str_replace('-', '', $arg);
    }

    public function arrayToObject($arr, $className)
    {
        $obj = new $className();
        foreach (get_object_vars($obj) as $name => $value){
            if (isset($arr[$name]))
                $obj->$name = $arr[$name];
        }
        return $obj;
    }
}
