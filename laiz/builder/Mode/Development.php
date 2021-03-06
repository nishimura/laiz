<?php
/**
 * Building Objects for Development Mode.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\builder;

use \laiz\core\Configure;

/**
 * Building Objects for Development Mode.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  1000  // This priority is lowest.
 */
class Mode_Development extends Mode_Base
{
    public function accept()
    {
        return true;
    }

    public function buildComponents(Container $container)
    {
        parent::buildComponents($container);

        /**
         * Building and Setting Request Object
         */
        if ($container->get('laiz.action.Request'))
            return;
        $req = $container->create('laiz.action.Request_Web',
                                  'laiz.action.Request');
        $reqArgs = Configure::get('laiz.action.Request');
        $req->setActionKey($reqArgs['ACTION_KEY']);
        $req->setPathInfoAction($reqArgs['PATH_INFO_ACTION']);
    }
}
