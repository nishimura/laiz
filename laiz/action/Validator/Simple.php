<?php
/**
 * Validation Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2007-2009 Satoshi Nishimura
 */

namespace laiz\action;

/**
 * Validation Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2007-2009 Satoshi Nishimura
 */
class Validator_Simple implements Validator
{
    /**
     * @param string $mail
     * @param bool $isDnsCheck DNSをチェックするかどうかのフラグ
     * @return bool
     * @access public
     */
    public function mail($mail, $isDnsCheck = false){
        if (!preg_match('/^[a-zA-Z0-9_]?[a-zA-Z0-9._+-]+@[a-zA-Z0-9-.]+\.[a-zA-Z]{2,4}$/', $mail)) {
            return false;
        }

        if (!$isDnsCheck)
            return true;

        if (!function_exists('checkdnsrr'))
            return true;

		list($user, $domain) = explode('@', $mail, 2);

		if(!checkdnsrr($domain, 'MX') && !gethostbynamel($domain)) {
            // If not exists MX recode then MX is host. (RFC.2821
			return false;
		}

        return true;
    }

    public function digit($arg)
    {
        return ctype_digit($arg);
    }

    /**
     * @param string $str
     * @param int $min
     * @return bool
     * @access public
     */
    public function minLength($str, $min){
        return (strlen($str) >= $min);
    }

    /**
     * @param string $str
     * @param string $max
     * @return bool
     * @access public
     */
    public function maxLength($str, $max){
        return (strlen($str) <= $max);
    }

    /**
     * @param string $str
     * @return bool
     * @access public
     */
    public function numeric($str){
        return is_numeric($str);
    }

    /**
     * @param string $num
     * @param int $min
     * @return bool
     * @access public
     */
    public function minValue($num, $min){
        if (!is_numeric($num) || !is_numeric($min)) return false;
        return ($num >= $min);
    }

    /**
     * @param string $num
     * @param int $max
     * @return bool
     * @access public
     */
    public function maxValue($num, $max){
        if (!is_numeric($num) || !is_numeric($max)) return false;
        return ($num <= $max);
    }

    /**
     * @param string $num
     * @param string $min
     * @param string $max
     * @return bool
     * @access public
     */
    public function between($num, $min, $max){
        if (!is_numeric($num) || !is_numeric($min) || !is_numeric($max)) return false;
        return ($num >= $min && $num <= $max);
    }

    /**
     * @param string $str
     * @return bool
     * @access public
     */
    public function alnum($str){
        return ctype_alnum($str);
    }

    /**
     * @param string $str
     * @return bool
     * @access public
     */
    public function alpha($str){
        return ctype_alpha($str);
    }

    /**
     * @param string $str
     * @return bool
     * @access public
     */
    public function lower($str){
        return ctype_lower($str);
    }

    /**
     * @param string $str
     * @return bool
     * @access public
     */
    public function upper($str){
        return ctype_upper($str);
    }

    /**
     * 必須チェック
     * 複数の値をチェックする場合は、どれか一つでも存在していれば良い
     *
     * @return bool
     * @access public
     */
    public function required(){
        $args = func_get_args();
        foreach ($args as $arg){
            if (strlen($arg) !== 0)
                return true;
        }
        return false;
    }

    /**
     * 簡易郵便番号チェック
     *
     * @param string $str1
     * @param string $str2
     * @return bool
     * @access public
     */
    public function zip($str1, $str2 = null){
        if ($str2 !== null)
            $str1 = $str1 . '-' . $str2;
        
        return (preg_match('/^[0-9]{3}-[0-9]{4}$/', $str1) == 1);
    }

    /**
     * @param string $str1
     * @param string $str2
     * @param string $str3
     * @return bool
     * @access public
     */
    public function phone($str1, $str2 = null, $str3 = null){
        if ($str2 != null && $str3 != null)
            $str1 = $str1 . '-' . $str2 . '-' . $str3;

        // 2〜5桁 - 1〜4桁 - 4桁
        return (preg_match('/^[0-9]{2,5}-[0-9]{2,4}-[0-9]{3,4}$/', $str1) == 1);
    }

    /**
     * @param string $str
     * @param string $equal
     * @return bool
     * @access public
     */
    public function equal($str, $equal){
        return ($str === $equal);
    }
}
