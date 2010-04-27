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

use \laiz\lib\aggregate\laiz\lib\test\ActionTests;

/**
 * Run Unit Test.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Action_ActionTest implements Describable
{
    public $arg1;
    public $arg2;
    public function act(ActionTests $tests)
    {
        if ($this->arg1 === '-h' || $this->arg1 === '--help')
            return $this->help();

        if (strlen(trim($this->arg1)) === 0)
            $this->allTest($tests);
        else
            $this->actionTest($tests);
    }

    private function actionTest(ActionTests $tests)
    {
        foreach ($tests as $test){
            $test->test();
        }
        echo "TODO: Run Test <" . $this->arg1 . ">\n";
    }

    private function allTest(ActionTests $tests)
    {
        echo "TODO: Run Test All Action.\n";
    }

    public function help()
    {
        echo "laiz.sh ActionTest          : All Action Tests.\n";
        echo "laiz.sh ActionTest <Action> : Action Test.\n";
        echo "\n";
    }

    public function describe()
    {
        return 'Run action test. -h option is display detail.';
    }
}
