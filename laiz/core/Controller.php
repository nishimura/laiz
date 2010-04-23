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
use laiz\action\Request;
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

        // create request and Response object
        $requestArgs = Configure::get('laiz.action.Request');
        $req = $container->create('laiz.action.Request');
        $req->setActionKey($requestArgs['ACTION_KEY']);
        $req->setPathInfoAction($requestArgs['PATH_INFO_ACTION']);

        // run action
        $actionName = $req->initActionName()->getActionName();
        $actionRunner = $container->create('laiz.action.Runner');
        $ret = $actionRunner->run($actionName);

        // TODO: separate to class of this process and variables
        if ($ret === 'none:')
            return;

        // create view object, setting and run
        $view = $container->create('laiz.view.View');
        $view->setTemplateDir($ret['templateDir']);

        // run view
        $res = $container->create('laiz.action.Response');
        $view->execute($res, $ret['templateName']);
    }
}
