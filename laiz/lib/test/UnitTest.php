<?php
/**
 * Interface for Unit Test.
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
 * Interface for Unit Test.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
interface UnitTest extends Aggregatable
{
    //
    // Not required method.
    //
    // /**
    //  * Unit Test method.
    //  */
    // public function test*(Assert $assert);
    //
    // /**
    //  * Setup method.
    //  */
    //  public function setup();
    //
    //  /**
    //   * Clean up method.
    //   */
    //  public function cleanup();
}
