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
// $Id: $
//


/**
* Class that represents a text string node.
*
*
*/

class Fly_Flexy_Token_Text extends Fly_Flexy_Token {


    /**
    * Simple check to see if this piece of text is a word
    * so that gettext and the merging tricks dont try
    * - merge white space with a flexy tag
    * - gettext doesnt translate &nbsp; etc.
    *
    * @return   boolean  true if this is a word
    * @access   public
    */
    function isWord() {
        if (!strlen(trim($this->value))) {
            return false;
        }
        if (preg_match('/^\&[a-z0-9]+;$/i',trim($this->value))) {
            return false;
        }
        return  preg_match('/\w/i',$this->value);
    }

}



