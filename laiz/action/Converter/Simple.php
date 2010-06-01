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

    public function half($arg)
    {
        $arg = $this->halfAlnum($arg);
        $arg = $this->halfSpace($arg);
        $arg = $this->halfHyphen($arg);
        return $arg;
    }

    public function halfAlnum($arg)
    {
        return mb_convert_kana($arg, 'a');
    }

    public function halfSpace($arg)
    {
        return mb_convert_kana($arg, 's');
    }

    public function halfHyphen($arg)
    {
        $arg = preg_replace("/([0-9])ー([0-9])/", "\$1-\$2", $arg);
        $arg = preg_replace("/([0-9])−([0-9])/", "\$1-\$2", $arg);
        $arg = preg_replace("/([0-9])‐([0-9])/", "\$1-\$2", $arg);
        $arg = preg_replace("/([0-9])\xa1\xbd([0-9])/", "\$1-\$2", $arg); // ダッシュ
        return $arg;
    }

    public function hexdec($arg)
    {
        return hexdec($arg);
    }
}
