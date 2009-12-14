<?php

/**
 * Class to handle loop statements
 *
 * @autor Satoshi Nishimura
 */
class Fly_Flexy_Token_Loop extends Fly_Flexy_Token {
    
    /**
    * variable to loop on. 
    *
    * @var string
    * @access public
    */
    var $loopOn = '';
    /**
    * optional value (in key=>value pair)
    *
    * @var string
    * @access public
    */
    var $value  = ''; 
    
    /**
    * Setvalue - a array of all three (last one optional)
    * @see parent::setValue()
    */
    function setValue($value) {
        $this->loopOn=$value[0];
        if (!isset($value[0]) || !strlen(trim($value[0]))) {
            // error condition.
            return false;
        }
        $this->value=$value[0];
    }
}
