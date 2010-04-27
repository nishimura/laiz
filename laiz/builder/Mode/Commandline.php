<?php
/**
 * Building Objects for Commandline Mode.
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
 * Building Objects for Commandline Mode.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  980
 */
class Mode_Commandline implements Mode
{
    public function accept()
    {
        if (isset($_SERVER['argv'], $_SERVER['argc']))
            return true;
        else
            return false;
    }

    public function buildComponents(Container $container)
    {
        /**
         * Building and Setting Request Object
         */
        $req = $container->create('laiz.action.Request_Commandline',
                                  'laiz.action.Request');
    }
}
