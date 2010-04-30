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

        $argIndex = 0;
        $setAction = false;
        for ($i = 1; $i < $_SERVER['argc']; $i++){
            $arg = $_SERVER['argv'][$i];
            switch (true){
            case (preg_match('/^-[a-zA-Z0-9]/', $arg)):
                $arg = ltrim($arg, '-');
                $chars = str_split($arg);
                foreach ($chars as $char){
                    $this->add($char, $char);
                }
                break;

            case (preg_match('/^--[a-zA-Z0-9]+=(.+)$/', $arg)):
                list ($key, $value) = explode('=', $arg, 2);
                $this->add(ltrim($key, '-'), $value);
                break;

            case (preg_match('/^--[a-zA-Z0-9]+$/', $arg)):
                $this->add(ltrim($arg, '-'), $arg);
                break;

            default:
                if ($argIndex === 0)
                    $this->add('action', ucfirst($arg));
                else
                    $this->add('arg' . $argIndex, $arg);
                $argIndex++;
                break;
            }
        }

        $this->add('argv', $_SERVER['argv']);
        $this->add('argc', $_SERVER['argc']);
    }

    public function initActionName()
    {
        return $this;
    }

    public function getActionName()
    {
        return $this->get('action');
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
        if (!isset($configs['args']) || !is_array($configs['args']))
            return;

        foreach ($configs['args'] as $key => $value){
            if (!is_numeric($value)){
                trigger_error("$value is not numeric with $key key in config ini file.");
                continue;
            }

            $argN = $this->get('arg' . $value);
            $this->add($key, $argN);
        }
    }
}
