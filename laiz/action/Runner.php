<?php
/**
 * Run Action.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

use \laiz\parser\Aggregate as ConfigParser; // ==check== TODO: YAML

use \laiz\lib\aggregate\laiz\action\Configurables;
use \laiz\lib\aggregate\laiz\action\Components;

/**
 * Class of Building and Running Action.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Runner
{
    const GLOBAL_CONFIG = 'laiz';

    private $container;
    private $parser;

    public function __construct(ConfigParser $parser
                                , Configurables $configurables
                                , Components $components
                                , Response $res)
    {
        $this->parser = $parser;
        $this->configurables = $configurables;
        $this->components = $components;
        $this->response = $res;
    }

    public function run($actionName)
    {
        $opts = $this->parseOpts($actionName);
        $configs = $this->getConfigs($opts['actionName']);
        return $this->runRoutine($opts['actionName'], $configs, $opts);
    }

    public function parseOpts($actionName)
    {
        $match = false;
        foreach ($this->configurables as $obj){
            if ($obj->match($actionName)){
                $componentAction = $obj->convertActionClassName($actionName);
                $methodName = $obj->getExecutionMethod();

                $templateDir = $obj->getTemplateDir();
                $templateName = $obj->convertTemplateName($actionName);
                $match = true;
                break;
            }
            // sure match Configurable_Default last.
        }

        $opts = array('actionName' => $componentAction,
                      'methodName' => $methodName,
                      'templateDir' => $templateDir,
                      'templateName' => $templateName);
        return $opts;
    }

    private function clean()
    {
        $this->response->clean();
        return $this;
    }

    private function runRoutine($action, $configs, $opts)
    {
        $ret = null;
        foreach ($this->components as $component){
            // Exception objects of setting configuration
            if ($component instanceof Component_Initializer){
                $cfg = $configs;
                goto run;
            }
            if ($component instanceof Component_Action){
                $cfg = $configs;
                $cfg['actionName'] = $opts['actionName'];
                $cfg['methodName'] = $opts['methodName'];
                // used there arguments, property and pathinfo
                goto run;
            }

            // unrecognized class name
            if (!preg_match('/([a-zA-Z_]+)$/', get_class($component), $matches))
                continue;
            $sectionName = str_replace('Component_', '', $matches[1]);
            $sectionName = strtolower($sectionName);

            // is not set in ini file
            if (!isset($configs[$sectionName]))
                continue;

            // General configuration
            $cfg = $configs[$sectionName];

        run:
            $ret = $component->run($cfg);
            if ($ret)
                break;
        }

        if ($ret === 'none:')
            return $ret;

        if (!$ret){
            if (isset($configs['result']['*']))
                return $this->clean()->run($configs['result']['*']);

            if (isset($configs['view']['*']))
                return array('templateDir' => $opts['templateDir'],
                             'templateName' => $configs['view']['*']);

            return array('templateDir' => $opts['templateDir'],
                         'templateName' => $opts['templateName']);
        }

        if (preg_match('/^redirect:/', $ret)){
            $redirect = str_replace('redirect:', '', $ret);
            header("Location: $redirect");
            return array('templateDir' => 'Base/templates/',
                         'templateName' => 'ErrorTemplate'); // ==check== debug
        }

        if (preg_match('/^action:/', $ret)){
            return $this->clean()->run(str_replace('action:', '', $ret));
        }

        if (isset($configs['result'][$ret]))
            return $this->clean()->run($configs['result'][$ret]);

        if (isset($configs['view'][$ret]))
            return array('templateDir' => $opts['templateDir'],
                         'view' => $configs['view'][$ret]);

        trigger_error("Not found [result] or [view] settings by return value [$ret]");

    }

    public function getConfigs($actionName)
    {
        $configFile = str_replace('\\', '/', $actionName);
        $configFile = '/' . str_replace('_', '/', $configFile);
        $tokens = explode('/', $configFile);
        $path = '';
        $configs = array();
        foreach ($tokens as $key => $token){
            $path = $path . $token;
            if ($key == count($tokens) - 1)
                $file = $path;
            else
                $file = $path . '/' . self::GLOBAL_CONFIG;

            $path .= '/';
            $configs = $this->mergeIniFile(ltrim($file, '/'), $configs);
        }
        return $configs;
    }

    private function mergeIniFile($iniFile, $configs)
    {
        $newConfigs = $this->parser->parse($iniFile);
        foreach ($newConfigs as $section => $value){
            if ($section === 'include'){
                foreach ($value as $includeFile){
                    // override:
                    //   global < upper section < include < under section
                    $configs = $this->mergeIniFile($includeFile, $configs);
                }
                continue;
            }

            if (isset($configs[$section]))
                $configs[$section] = array_merge($configs[$section], $value);
            else
                $configs[$section] = $value;
        }
        return $configs;
    }
}
