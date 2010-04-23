<?php
/**
 * Class file of keep validated variables for action classes.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\validator;

use laiz\action\Persistence;
use laiz\action\Request;
use \StdClass;

/**
 * Class of keep validated variables for action classes.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Response implements Persistence
{
    private $errors = array();
    private $request;

    public function __construct(Request $req)
    {
        $this->request = $req;
    }

    public function set($key, $val)
    {
        $this->errors[$key] = $val;
    }

    public function getClass()
    {
        $a = new StdClass();
        foreach ($this->errors as $key => $val)
            $a->$key = $val;
        $this->request->setPropertiesByRequest($a);

        return $a;
    }
}
