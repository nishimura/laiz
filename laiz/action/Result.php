<?php
/**
 * Result for Output.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

use \laiz\builder\Singleton;
use \laiz\builder\Aggregatable;

/**
 * Result for Output.
 *
 * @see       \laiz\action\Component_Initializer 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface Result extends Singleton, Aggregatable
{
}
