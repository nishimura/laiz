<?php
/**
 * Run Unit or Action Test.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\command;

/**
 * Run Unit or Action Test.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Action_Test implements Describable, Help
{
    public $arg1;
    public function act()
    {
        if ($this->arg1 === 'action')
            return 'action:Test_Action';
        if ($this->arg1 === 'unit')
            return 'action:Test_Unit';

        echo "This command needs more argument.\n\n";
        echo $this->help() . "\n";
    }

    public function describe()
    {
        return 'Run unit or action test.';
    }

    public function help()
    {
        $ret
            = "laiz.sh test action    : Testing Actions.\n"
            . "laiz.sh test action -h : Show more help.\n"
            . "laiz.sh test unit      : Testing Unit Testcases.\n"
            . "laiz.sh test unit -h   : Show more help.";
        return $ret;
    }
}
