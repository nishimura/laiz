<?php
/**
 * Abstract Class for Building Objects.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\builder;

/**
 * Abstract Class for Building Objects.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
abstract class Mode_Base implements Mode
{
    public function buildComponents(Container $container)
    {
        // common initialization

        /* $container->create('laiz.action.Response_Singleton',
         *                    'laiz.action.Response'); */
        // see laiz/action/Response.ini
    }
}
