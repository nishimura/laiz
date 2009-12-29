<?php
/**
 * MVC Controller Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 */

/**
 * MVC Controller Class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Controller
{
    /**
     * Start MVC controller.
     *
     * @access public
     */
    public function execute(){
        // create container with configure
        $container = Laiz_Container::getInstance(Laiz_Configure::get('Laiz_Container'));

        // setup autoload
        Laiz_Autoload::walk($container->getComponents('Laiz_Autoload_Component'));

        // create request object
        $requestArgs = Laiz_Configure::get('Laiz_Request');
        $req = $container->create('Laiz_Request');
        $req->setActionKey($requestArgs['ACTION_KEY']);
        $req->setPathInfoAction($requestArgs['PATH_INFO_ACTION']);

        // create view object
        $configs = Laiz_Configure::get('Laiz_View');
        $view = $container->create('Laiz_View');
        $view->init($configs);

        // run action
        $req = $container->getComponent('Laiz_Request');
        $actionName = $req->initActionName()->getActionName();
        $actionRunner = $container->create('Laiz_Action_Runner', 200);
        $ret = $actionRunner->run($actionName);

        // setting view
        $view->setTemplateDir($ret['templateDir']);
        $view->setTemplate($ret['view']);

        // run display filter
        // TODO: separate to class of this process
        $displays = $container->getComponents('Laiz_Action_Display');
        $displayResult = array();
        foreach ($displays as $a){
            $obj = $a->getClass();
            $req->setPropertiesByRequest($obj);
            $displayResult[] = $container->execMethod($obj, $a->getMethod());
        }
        foreach ($displayResult as $r){
            switch ($r){
            case 'none':
                return;

            default:
                break;
            }
        }

        // run view
        $view->execute();
    }
}
