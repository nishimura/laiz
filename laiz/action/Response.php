<?php
/**
 * Abstract Class of response for View.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

use \StdClass;

/**
 * Abstract Class of Response for View.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
abstract class Response
{
    protected $objects = array();

    protected $templateDir;
    protected $templateName;

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

        $this->templateDir = null;
        $this->templateName = null;

        return $this;
    }

    public function setTemplateDir($dir)
    {
        $this->templateDir = $dir;
        return $this;
    }

    public function setTemplateName($tmpl)
    {
        $this->templateName = $tmpl;
        return $this;
    }

    public function getTemplateDir()
    {
        return $this->templateDir;
    }

    public function getTemplateName()
    {
        return $this->templateName;
    }
}
