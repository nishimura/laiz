<?php
/**
 * File of response for view.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

use \laiz\builder\Singleton;
use \StdClass;

/**
 * Response for view.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Response implements Singleton
{
    private $objects = array();

    public function addObject($obj)
    {
        if (!is_object($obj))
            trigger_errro("$obj is not Object.", E_USER_WARNING);

        $this->objects[] = $obj;
        return $this;
    }

    public function getObject()
    {
        $ret = new StdClass();
        foreach ($this->objects as $obj){
            $properties = get_object_vars($obj);
            foreach ($properties as $key => $value){
                if (!preg_match('/^_/', $key))
                    $ret->$key = $obj->$key;
            }
        }

        return $ret;
    }

    public function clean()
    {
        $objects = $this->objects;
        $this->objects = array();
        foreach ($objects as $key => $obj){
            if ($obj instanceof Persistence)
                $this->objects[] = $obj;
        }

        return $this;
    }
}
