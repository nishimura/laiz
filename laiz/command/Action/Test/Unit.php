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

use \laiz\lib\aggregate\laiz\lib\test\UnitTests;
use \laiz\lib\test\Assert;

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
    public $a;
    public $v;
    public function act(UnitTests $tests)
    {
        if ($this->h || $this->help){
            echo $this->help();
            return;
        }

        if ($this->arg1 === 'unit')
            $arg = $this->arg2; // from Action_Test
        else
            $arg = $this->arg1; // direct

        // init assert object.
        if ($this->a)
            $assertOption = Assert::VIEW_ALL;
        else
            $assertOption = Assert::VIEW_FAILURE;
        if ($this->v)
            $assertOption |= Assert::VIEW_VERBOSE;
        $assert = new Assert($assertOption);

        if (strlen(trim($arg)) === 0)
            $this->allTest($tests, $assert);
        else
            echo "TODO: Run Test <" . $arg . ">\n";
    }

    private function allTest(UnitTests $tests, Assert $assert)
    {
        foreach ($tests as $test){
            $methods = get_class_methods($test);
            foreach ($methods as $method){
                if (!preg_match('/^test/', $method))
                    continue;
                if ($method === 'setup' || $method === 'cleanup')
                    continue;


                if (method_exists($test, 'setup'))
                    $test->setup($assert);

                $test->$method($assert);

                if (method_exists($test, 'cleanup'))
                    $test->cleanup($assert);
            }
        }

        $assert->showResult();
    }

    public function help()
    {
        $ret
            = "laiz test unit          : All Tests.\n"
            . "laiz test unit <Class>  : Unit Test.\n"
            . "laiz test unit -a       : View Success.\n"
            . "laiz test unit -v       : View Verbose.\n";
        return $ret;
    }
}
