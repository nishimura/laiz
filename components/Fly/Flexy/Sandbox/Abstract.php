<?php
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
// +----------------------------------------------------------------------+
//
// $Id: Abstract.php 23 2009-10-07 23:25:34Z tomoaki $
//

abstract class Fly_Flexy_Sandbox_Abstract
{
    /**
     * Flexy frontend object
     *
     * @var Fly_Flexy
     */
    private $_flexy;

    private $_allowedOptions = array(
    								'defaultplugin',
                                    );

    public function __construct($flexy)
    {
        $this->_flexy = $flexy;

        if (isset($flexy->options['allowedOptions'])) {
            $this->_allowedOptions = (array) $flexy->options['allowedOptions'];
        }

    }

    public function set($name, $value)
    {
        $name = strtolower($name);
        if (array_search($name, $this->_allowedOptions)) {
            $this->_{$name} = $value;
        }
    }

    public function plugin()
    {

    }

    public function __call()
    {

    }
}
?>