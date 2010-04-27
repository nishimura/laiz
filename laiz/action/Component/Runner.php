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
        $obj = Builder::build($className);

        $container = Container::getInstance();
        $response  = $container->create('laiz.action.Response');
        $response->addObject($obj);

        self::prepare($obj, $config);
        return self::exec($obj, $methodName);
    }

    static public function prepare($obj, Array $config)
    {
        $container = Container::getInstance();
        $request = $container->create('laiz.action.Request');
        $request->setRequestsByConfigs($config);
        Util::setPropertiesByRequest($request, $obj);
        Builder::initObject($obj, $config);
        return $obj;
    }

    static public function exec($obj, $methodName)
    {
        return Builder::execMethod($obj, $methodName);
    }
}
