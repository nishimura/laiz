<?php
/**
 * Class file of keep validated variables for action classes.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2009 Satoshi Nishimura
 */

/**
 * Class of keep validated variables for action classes.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2009 Satoshi Nishimura
 */
class Laiz_Validator_Response implements Laiz_Action_Response, Laiz_Action_Persistence
{
    private $errors = array();
    private $request;

    public function __construct(Laiz_Request $req)
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
