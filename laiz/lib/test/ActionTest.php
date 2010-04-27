<?php
/**
 * Interface for Action Test.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\test;

use \laiz\builder\Aggregatable;

/**
 * Interface for Action Test.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface ActionTest extends Aggregatable
{
    public function test();
}
