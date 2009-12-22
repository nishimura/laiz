<?php
/**
 * Autoload Interface
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2009 Satoshi Nishimura
 */

/**
 * Autoload Interface
 *
 * Autoload setting by any classes
 * when Implemented this interface and setting in components.ini.
 *
 * @package Laiz
 * @author  Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2009 Satoshi Nishimura
 */
interface Laiz_Autoload_Component extends Laiz_Component
{
    public function autoload($name);
}
