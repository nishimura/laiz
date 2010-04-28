<?php
/**
 * MVC Controller Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\core;

use laiz\autoloader\BasicLoader;
use laiz\builder\Container;
use laiz\builder\Object as Builder;
use laiz\action\Runner;

/**
 * MVC Controller Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Controller
{
    /**
     * Start MVC controller.
     *
     * @access public
     */
    public function execute(){
        // create container with configure
        $container = Container::getInstance();

        // setup autoload
        BasicLoader::walk($container->getComponents('laiz.autoloader.Register'));

        // create request object
        $req = $container->get('laiz.action.Request');

        // run action
        // view is action component and run in action runner
        $actionName = $req->initActionName()->getActionName();
        $actionRunner = $container->create('laiz.action.Runner');
        $actionRunner->run($actionName);
    }
}
