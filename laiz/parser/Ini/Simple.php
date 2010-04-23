<?php
/**
 * Class of parsing ini file using simple original method.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\parser;

/**
 * Class of parsing ini file using simple original method.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Ini_Simple extends Ini
{
    public function parseIniFile($fileName, $flag = false)
    {
        if (!$lines = @file($fileName, 1))
            return false;

        $res  = array();
        $curr = & $res;

        $sectionPattern  = '/^\[([^\]]+)\]/';
        $linePattern     = '/^\s*([^=;]*)\s*=\s*(.*?)\s*$/';
        $valuePattern    = '/^"(.*?)"\s*(?:;.*)?$/';
        $commentPattern  = '/^([^;]*?)\s*;/';

        foreach ($lines as $line){
            $line = trim($line);
            if (strlen($line) === 0)
                continue;
		
            if (preg_match($sectionPattern, $line, $m)){
                // parse section
                $key = trim($m[1]);
                $res[$key] = array();
                $curr = & $res[$key];
			
            }elseif(preg_match($linePattern, $line, $m)){
                // parse key and value
                $key   = trim($m[1]);
                $value = trim($m[2]);
                if ($value !== '' && $value{0} === '"'){
                    if (preg_match($valuePattern, $value, $m))
                        $value = $m[1];
                }else if(preg_match($commentPattern, $value, $m)){
                    $value = $m[1];
                }
				
                $curr[$key] = $value;
            }
        }
        return $res;
    }
}
