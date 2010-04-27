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
use \laiz\lib\test\Assert;
use \laiz\action\Runner;
use \laiz\action\Component_Runner;
use \laiz\builder\Container;
use \laiz\builder\Object as Builder;

/**
 * Run Unit Test.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Action_ActionTest implements Describable
{
    public $h;
    public $help;
    public $a;
    public $v;
    public function act(ActionTests $tests, Container $container)
    {
        if ($this->h || $this->help)
            return $this->help();

        $this->allTest($tests, $container);
    }

    private function allTest(ActionTests $tests, $container)
    {
        if ($this->a)
            $assertOption = Assert::VIEW_ALL;
        else
            $assertOption = Assert::VIEW_FAILURE;

        if ($this->v)
            $assertOption |= Assert::VIEW_VERBOSE;

        $assert = new Assert($assertOption);

        // commandline temporarily off
        $commandConfigurable = $container->get('laiz.command.Action');
        $commandConfigurable->setUnuse();
        foreach ($tests as $test){
            /*
             * Prepare
             */
            $actionName = $test->getActionName();
            // new clean instance
            $actionRunner = $container->create('laiz.action.Runner');
            $opts = $actionRunner->parseOpts($actionName);
            $configs = $actionRunner->getConfigs($opts['actionName']);
            Component_Runner::prepare($test, $configs);
            /*
             * Testing Prepare
             */
            if (method_exists($test, 'testProp'))
                $test->testProp($assert);

            /*
             * Testing result
             */
            $ret = Component_Runner::exec($test, $opts['methodName']);
            //==TODO==

            /*
             * Testing Run
             */
            $methods = get_class_methods($test);
            foreach ($methods as $method){
                if (!preg_match('/^test/', $method))
                    continue;
                if ($method === 'testPrep')
                    continue;

                // new clean instance
                $action = $container->create($opts['actionName']);
                Component_Runner::prepare($action, $configs);
                Component_Runner::exec($action, $opts['methodName']);

                $action->$method($assert);
            }
        }

        $assert->showResult();

        $commandConfigurable->setUse();
    }

    public function help()
    {
        echo "laiz.sh ActionTest    : All Action Tests.\n";
        echo "laiz.sh ActionTest -a : View Success.\n";
        echo "laiz.sh ActionTest -v : View Verbose.\n";
        echo "\n";
    }

    public function describe()
    {
        return 'Run action test. -h option is display detail.';
    }
}
