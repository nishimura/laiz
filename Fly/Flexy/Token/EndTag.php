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
* The closing HTML Tag = eg. /Table or /Body etc.
*
* @abstract
* This just extends the generic HTML tag
*
*/

require_once 'Fly/Flexy/Token/Tag.php';


class Fly_Flexy_Token_EndTag extends Fly_Flexy_Token_Tag { }

