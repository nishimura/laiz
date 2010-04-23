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
    private $request;

    public function __construct(Response $res, Request $req)
    {
        $this->response = $res;
        $this->request  = $req;
    }

    public function run(Array $config)
    {
        $actionName = $config['actionName'];
        $methodName = $config['methodName'];

        $fileExists = false;
        $classPath = str_replace('_', '/', $actionName);
        foreach (explode(PATH_SEPARATOR, ini_get('include_path')) as $path){
            if (file_exists($path . "/$classPath.php")){
                $fileExists = true;
                break;
            }
        }
        if (!$fileExists)
            return;

        return Component_Runner::run($actionName, $config, $methodName);
    }
}
