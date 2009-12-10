<?php
/**
 * None Display Filter Class File.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2009 Satoshi Nishimura
 */

/**
 * None Display Filter Class.
 *
 * If the display method is excuted then not execute a view method.
 *
 * @package Laiz
 * @author  Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2009 Satoshi Nishimura
 */
class Laiz_Display_None
{
    /**
     * None Display Filter.
     *
     * Write a ini file of action,
     * <code>
     * [display]
     * none = Laiz_Display_None
     * </code>
     * then view method does not be executed.
     * 
     * @author    Satoshi Nishimura <nishim314@gmail.com>
     */
    public function display()
    {
        return 'none';
    }
}
