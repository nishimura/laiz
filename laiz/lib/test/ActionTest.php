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
    //
    // Not required method.
    //
    // /**
    //  * Prepared property test.
    //  */
    // public function testPrep(Assert $assert);
    //
    // /**
    //  * Test Action.
    //  *
    //  * @ActionTest request:argName=argValue
    //  * @ActionTest return:returnValue
    //  */
    // public function test*(Assert $assert);
    //

    /**
     * Returns action name when implemented class running.
     * @return string
     */
    public function getActionName();
}
