<?php
/**
 * File of Laiz_Result setting class.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * Stored variables class for View.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Action_Response_Result implements Laiz_Component, Laiz_Action_Response, Laiz_Action_Persistence
{
    private $result;

    public function __construct(Laiz_Result $res)
    {
        $this->result = $res;
    }

    public function getClass()
    {
        return $this->result;
    }
}
