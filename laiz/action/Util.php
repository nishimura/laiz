<?php
/**
 * Utility Class for Action.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\action;

/**
 * Utility Class for Action.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Util
{
    /**
     * Set class property by request.
     *
     * @param Object $obj
     * @access public
     */
    static public function setPropertiesByRequest(Request $req, $obj){
        $properties = get_object_vars($obj);
        foreach ($properties as $property => $var){
            // not include variable that starts underscore
            if (preg_match('/^([^_].*)/', $property, $matches)){
                $value = $req->get($matches[1]);

                if ($value !== null){
                    $obj->$property = $value;
                }
            }
        }
    }
}
