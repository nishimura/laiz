<?php
/**
 * Describable Interface.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\command;

use \laiz\builder\Aggregatable;

/**
 * Describable Interface.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Describable extends Aggregatable
{
    public function describe();
}
