<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// This Package is based upon PEAR::HTML_Template_Flexy (ver 1.3.9 (stable) released on 2009-03-24)
//  Please visit http://pear.php.net/package/HTML_Template_Flexy
//
// +----------------------------------------------------------------------+
// | PHP Version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author:  Tomoaki Kosugi <kosugi@kips.gr.jp>                          |
// | Authors:  Alan Knowles <alan@akbkhome>                               |
// +----------------------------------------------------------------------+
//
// $Id:$
//

/**
* Class to handle foreach statements
*
*
*/
class Fly_Flexy_Token_Foreach extends Fly_Flexy_Token {

    /**
    * variable to loop on.
    *
    * @var string
    * @access public
    */
    var $loopOn = '';
    /**
    * key value
    *
    * @var string
    * @access public
    */
    var $key    = '';
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
        if (!isset($value[1]) || !strlen(trim($value[1]))) {
            // error condition.
            return false;
        }
        $this->key=$value[1];
        $this->value=@$value[2];
    }


}




