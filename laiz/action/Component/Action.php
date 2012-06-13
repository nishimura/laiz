<?php
/**
 * Class file of filter component.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

use \laiz\action\Validator_Result;
use \laiz\core\Configure;

/**
 * Class of filter component.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  200
 */
class Component_Action implements Component
{
    private $response;
    private $validatorResult;

    public function __construct(Response $res, Validator_Result $vr)
    {
        $this->response = $res;
        $this->validatorResult = $vr;
    }

    public function run(Array $config)
    {
        $actionName = $config['actionName'];

        $fileExists = false;
        $classPath = str_replace('_', '/', $actionName);
        $classPath = str_replace('\\', '/', $classPath);
        foreach (explode(PATH_SEPARATOR, ini_get('include_path')) as $path){
            if (file_exists($path . "/$classPath.php")){
                $fileExists = true;
                break;
            }
        }
        if (!$fileExists)
            return;

        $methodName = $config['methodName'];

        $config = Configure::get('laiz.action.Validator');
        $handleByMethod = (boolean) $config['handleByMethod'];
        if ($handleByMethod){
            if (isset($this->validatorResult->_success)){
                if ($this->validatorResult->_success)
                    $methodName = 'valid';
                else
                    $methodName = 'invalid';
            }
        }
        $ret = Component_Runner::run($actionName, $config, $methodName);
        if (isset($config['result']['*']) && !$ret)
            $ret = 'action:' . $config['result']['*'];
        return $ret;
    }
}
