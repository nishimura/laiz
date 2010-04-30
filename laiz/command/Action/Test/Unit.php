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
class Action_Test_Unit
{
    public $h;
    public $help;
    public $arg1;
    public $arg2;
    public function act()
    {
        if ($this->h || $this->help){
            echo $this->help() . "\n";
            return;
        }

        if ($this->arg1 === 'unit')
            $arg = $this->arg2; // from Action_Test
        else
            $arg = $this->arg1; // direct

        if (strlen(trim($arg)) === 0)
            echo "TODO: Run Test All Action.\n";
        else
            echo "TODO: Run Test <" . $arg . ">\n";
    }

    public function help()
    {
        $ret
            = "laiz.sh UnitTest          : All Tests.\n"
            . "laiz.sh UnitTest <Class>  : Unit Test.";
        return $ret;
    }
}
