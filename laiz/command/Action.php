<?php
/**
 * Class file of command line action.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\command;

use \laiz\action\Configurable_Template;
use \laiz\builder\Singleton;

/**
 * Class of command line action.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  980
 */
class Action extends Configurable_Template implements Singleton
{
    private $forceUnuse = false;

    public function match($actionName)
    {
        if ($this->forceUnuse)
            return false;

        if (isset($_SERVER['argv'], $_SERVER['argc']))
            return true;
        else
            return false;
    }

    public function getActionNameSuffix($actionName)
    {
        if (strlen(trim($actionName)) === 0)
            $actionName = 'Default';
        return $actionName;
    }

    public function setUse()
    {
        $this->forceUnuse = false;
    }

    public function setUnuse()
    {
        $this->forceUnuse = true;
    }
}
