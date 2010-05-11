<?php
/**
 * Hash Utility Class.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\util;

/**
 * Hash Utility Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Hash
{
    static public function crypt($str, $salt)
    {
        if (!preg_match('/^\$5\$/', $salt)){
            $deletePrefix = true;
            $salt = '$5$' . $salt;
        }else{
            $deletePrefix = false;
        }

        $ret = crypt($str, $salt);
        if ($deletePrefix)
            $ret = preg_replace('/^\$5\$/', '', $ret);
        return $ret;
    }

    /**
     * @param string $str
     * @param bool $addPrefix If this flag is true then add '$5$'
     */
    static public function create($str, $addPrefix = false)
    {
        $saltLen = 16;
        for ($salt = '', $i = 0; $i < $saltLen; $i++)
            $salt .= chr(mt_rand(66, 122));
        $salt .= '$';
        if ($addPrefix)
            $salt = '$5$' . $salt;

        return self::crypt($str, $salt);
    }
}

if (!isset($_SERVER['argv'][0]) || realpath($_SERVER['argv'][0]) !== __FILE__)
    return 1;

// test
$str = 'foobar';
$hash = Hash::create($str);
echo 'hash: ', $hash, "\n";

echo 'Hash::crypt($str) == $hash?: ';
var_export((Hash::crypt($str, $hash) === $hash));
echo "\n\n";

$hash = Hash::create($str, true);
echo 'hash: ', $hash, "\n";

echo 'Hash::crypt($str) == $hash?: ';
var_export((Hash::crypt($str, $hash) === $hash));
echo "\n\n";
