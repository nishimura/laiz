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

use \ReflectionObject;

/**
 * Run Unit Test.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Action_Test_Action
{
    public $h;
    public $help;
    public $a;
    public $v;
    public function act(ActionTests $tests, Container $container)
    {
        if ($this->h || $this->help){
            echo $this->help();
            return;
        }

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
                // initialize request arguments
                $ref = new ReflectionObject($action);
                $refMethod = $ref->getMethod($method);
                $comment = $refMethod->getDocComment();
                if ($comment && preg_match_all("/@ActionTest +request:(.+)/", $comment, $matches)){
                    foreach ($matches[1] as $line){
                        $requests = explode('=', $line, 2);
                        if (count($requests) !== 2)
                            continue;
                        if (!property_exists($action, $requests[0]))
                            continue;
                        $action->$requests[0] = $requests[1];
                    }
                }

                if ($comment && preg_match('/@ActionTest +selfact/', $comment, $matches)){
                    // need call act method yourself
                    $ret = $action->$method($assert);
                }else{
                    if ($comment && preg_match('/@ActionTest +arguments:(.+)/', $comment, $matches)){
                        // call act method with mock arguments
                        $args = array();
                        foreach (explode(',', $matches[1]) as $arg){
                            $args[] = $container->create(trim($arg));
                        }
                        $ret = call_user_func_array(array($action, $opts['methodName']), $args);
                        array_unshift($args, $assert);
                        call_user_func_array(array($action, $method), $args);

                    }else{
                        $ret = Component_Runner::exec($action, $opts['methodName']);
                        $action->$method($assert);
                    }
                }

                if ($comment && preg_match("/@ActionTest +return:(.+)/", $comment, $matches)){
                    $this->testReturnValue($assert, $ret, $matches[1]);
                }
            }
        }

        $assert->showResult();

        $commandConfigurable->setUse();
    }

    private function testReturnValue(Assert $assert, $ret, $equal)
    {
        $assert->equal($ret, $equal);
    }

    public function help()
    {
        $ret
            = "laiz test action    : All Action Tests.\n"
            . "laiz test action -a : View Success.\n"
            . "laiz test action -v : View Verbose.\n";
        return $ret;
    }
}
