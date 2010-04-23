<?php
/**
 * None Display Filter Class File.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\lib\display;

/**
 * None Display Filter Class.
 *
 * If the display method is excuted then not execute a view method.
 *
 * @package Laiz
 * @author  Satoshi Nishimura <nishim314@gmail.com>
 */
class None
{
    /**
     * None Display Filter.
     *
     * Write a ini file of action,
     * <code>
     * [display]
     * none = laiz\lib\display\None
     * </code>
     * then view method does not be executed.
     * 
     * @author    Satoshi Nishimura <nishim314@gmail.com>
     */
    public function display()
    {
        return 'none:';
    }
}
