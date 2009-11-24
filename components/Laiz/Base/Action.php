<?php
/**
 * Class file of default base action.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 */

/**
 * Class of default base action.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Base_Action extends Laiz_Action_Config_Simple
{
    public function match($actionName)
    {
        return parent::$continueBase;
    }

    public function getBaseDir()
    {
        return 'Base';
    }

    public function getActionBase()
    {
        return 'Base_Action';
    }
}
