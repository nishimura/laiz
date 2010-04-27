<?php
/**
 * Request for Commandline Class.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\action;

/**
 * Request for Commandline Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Request_Commandline implements Request
{
    private $params = array();

    public function __construct()
    {
        if (!isset($_SERVER['argv'], $_SERVER['argc']))
            return;

        $argIndex = 1;
        $setAction = false;
        for ($i = 1; $i < $_SERVER['argc']; $i++){
            if (!$setAction){
                // action is first argument excluding /^-/
                if (!preg_match('/^-/', $_SERVER['argv'][$i])){
                    $this->add('action', $_SERVER['argv'][$i]);
                    $setAction = true;
                    continue;
                }
            }
            $this->add('arg' . $argIndex, $_SERVER['argv'][$i]);
            $argIndex++;
        }
    }

    public function initActionName()
    {
        return $this;
    }

    public function getActionName()
    {
    }

    public function add($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function get($name)
    {
        if (isset($this->params[$name]))
            return $this->params[$name];
        else
            return null;
    }

    public function setRequestsByConfigs(Array $configs)
    {

    }
}
