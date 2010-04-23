<?php
/**
 * Action Component Runner.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\action;

use laiz\builder\Container;
use laiz\builder\Object as Builder;

/**
 * Action Component Runner.
 *
 * Build action object and configure and run.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Component_Runner
{
    static public function run($className, Array $config, $methodName)
    {
        $container = Container::getInstance();
        $response  = $container->create('laiz.action.Response');
        $request   = $container->create('laiz.action.Request');

        $obj = Builder::build($className);
        $response->addObject($obj);
        $request->setRequestsByPathInfo($config);
        $request->setPropertiesByRequest($obj);
        Builder::initObject($obj, $config);
        return Builder::execMethod($obj, $methodName);
    }
}
