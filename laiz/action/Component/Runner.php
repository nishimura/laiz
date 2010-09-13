<?php
/**
 * Action Component Runner.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\action;

use \laiz\builder\Container;
use \laiz\builder\Object as Builder;
use \laiz\parser\Ini_Simple;

use \ReflectionObject;

/**
 * Action Component Runner.
 *
 * Build action object and configure and run.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Component_Runner
{
    static public function run($className, Array $config, $methodName)
    {
        $obj = Builder::build($className);

        $container = Container::getInstance();
        $response  = $container->create('laiz.action.Response');
        $response->addObject($obj);

        self::prepare($obj, $config);

        $ret = self::runPreparer($obj, $methodName);
        if ($ret)
            return $ret;

        return self::exec($obj, $methodName);
    }

    static private function runPreparer($obj, $methodName)
    {
        $ref = new ReflectionObject($obj);
        $refMethod = $ref->getMethod($methodName);
        $comment = $refMethod->getDocComment();
        if ($comment && preg_match_all('/@preparer +([a-zA-Z0-9_]+) +([a-zA-Z0-9_]+)/', $comment, $matches, PREG_SET_ORDER)){
            foreach ($matches as $line){
                $className = $line[1];
                $propName = $line[2];
                $iniFile = str_replace('_', '/', $className);
                $parser = new Ini_Simple();
                $configs = $parser->parseIniFile("$iniFile.ini", true);
                $preparer = Builder::build($className);
                if (!$preparer instanceof Preparer){
                    trigger_error($className . ' is not implemented laiz.action.Preparer.');
                    continue;
                }

                self::prepare($preparer, (array)$configs);

                $setter = function ($val) use ($obj, $propName){
                    $obj->$propName = $val;
                };
                $ret = $preparer->prepare($setter);
                if ($ret)
                    return $ret;
            }
            return null;
        }else{
            return null;
        }
    }

    static public function prepare($obj, Array $config)
    {
        $container = Container::getInstance();
        $request = $container->create('laiz.action.Request');
        $request->setRequestsByConfigs($config);
        Util::setPropertiesByRequest($request, $obj);
        Builder::initObject($obj, $config);
        return $obj;
    }

    static public function exec($obj, $methodName)
    {
        return Builder::execMethod($obj, $methodName);
    }
}
