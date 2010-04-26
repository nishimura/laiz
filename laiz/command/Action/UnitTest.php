<?php
/**
 * Run Unit Test.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\command;

/**
 * Run Unit Test.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Action_UnitTest implements Describable
{
    public function act()
    {
    }

    public function describe()
    {
        return 'Run unit test.';
    }
}
