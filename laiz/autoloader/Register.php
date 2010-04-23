<?php
/**
 * Autoload Interface
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\autoloader;

use \laiz\builder\Aggregatable;

/**
 * Autoload Interface
 *
 * Autoload setting by any classes
 * when Implemented this interface and setting in components.ini.
 *
 * @package Laiz
 * @author  Satoshi Nishimura <nishim314@gmail.com>
 */
interface Register extends Aggregatable
{
    public function autoload($name);
}
