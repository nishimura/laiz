<?php
/**
 * Class file of default base action.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

/**
 * Class of default base action.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  1000  // This priority is lowest.
 */
class Configurable_Default extends Configurable_Template
{
    public function match($actionName)
    {
        return true;
    }

    public function getActionNameSuffix($actionName)
    {
        if (strlen(trim($actionName)) === 0)
            $actionName = 'Top';
        return $actionName;
    }

    public function getPrefix()
    {
        return 'Base_';
    }
}
