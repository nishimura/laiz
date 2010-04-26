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
    public $arg1;
    public $arg2;
    public function act()
    {
        if ($this->arg1 === '-h' || $this->arg1 === '--help')
            return $this->help();

        if (strlen(trim($this->arg1)) === 0)
            echo "TODO: Run Test All Action.\n";
        else
            echo "TODO: Run Test <" . $this->arg1 . ">\n";
    }

    public function help()
    {
        echo "laiz.sh UnitTest          : All Test.\n";
        echo "laiz.sh UnitTest <action> : Action Test.\n";
        echo "\n";
    }

    public function describe()
    {
        return 'Run unit test. -h option is display detail.';
    }
}
