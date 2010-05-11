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
    private $debugLoopCount = 0;

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
        $this->debugLoopCount++;
        if ($this->debugLoopCount > 100){
            echo debug_backtrace();
            return;
        }

        $opts = $this->parseOpts($actionName);
        $configs = $this->getConfigs($opts['actionName']);
        return $this->runRoutine($configs, $opts);
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

    /**
     * Run action components.
     *
     * @param Array $configs get from ini file
     * @param Array $opts get from Configurable for action and view
     */
    private function runRoutine($configs, $opts)
    {
        $ret = null;

        // setting components configs
        if (isset($configs['view']['*']))
            $opts['templateName'] = $configs['view']['*'];
        $coConfigs = $configs;
        $coConfigs['action'] = $configs;
        $coConfigs['action']['actionName'] = $opts['actionName'];
        $coConfigs['action']['methodName'] = $opts['methodName'];
        $coConfigs['initializer'] = $configs;
        $coConfigs['viewconfigure'] = array();
        $coConfigs['viewconfigure']['templateDir'] = $opts['templateDir'];
        $coConfigs['viewconfigure']['templateName'] = $opts['templateName'];
        $coConfigs['viewrunner'] = array();

        foreach ($this->components as $component){
            // unrecognized class name
            if (!preg_match('/([a-zA-Z_]+)$/', get_class($component), $matches))
                continue;
            $sectionName = str_replace('Component_', '', $matches[1]);
            $sectionName = strtolower($sectionName);

            // is not set in ini file
            if (!isset($coConfigs[$sectionName]))
                continue;

            $ret = $component->run($coConfigs[$sectionName]);
            if ($ret){
                /*
                 * parsing return value
                 */
                if ($ret === 'none:')
                    return;

                // action redirect
                if (preg_match('/^redirect:/', $ret)){
                    $redirect = str_replace('redirect:', '', $ret);
                    header("Location: $redirect");
                    return;
                }else if (preg_match('/^action:/', $ret)){
                    return $this->clean()->run(str_replace('action:', '', $ret));
                }else if (isset($configs['result'][$ret])){
                    return $this->clean()->run($configs['result'][$ret]);
                }

                // change view
                else if (isset($configs['view'][$ret])){
                    $coConfigs['viewconfigure']['templateName']
                        = $configs['view'][$ret];
                }else{
                    // ini file configuration error
                    trigger_error("Not found [result] or [view] settings by return value [$ret]");
                }
            }
        }
    }

    public function getConfigs($actionName)
    {
        $configFile = str_replace('\\', '/', $actionName);
        $configFile = str_replace('_', '/', $configFile);
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
