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
class Mode_Development implements Mode
{
    public function accept()
    {
        return true;
    }

    public function buildComponents(Container $container)
    {
        /**
         * Building and Setting Request Object
         */
        $req = $container->create('laiz.action.Request_Web',
                                  'laiz.action.Request');
        $reqArgs = Configure::get('laiz.action.Request');
        $req->setActionKey($reqArgs['ACTION_KEY']);
        $req->setPathInfoAction($reqArgs['PATH_INFO_ACTION']);
    }
}
