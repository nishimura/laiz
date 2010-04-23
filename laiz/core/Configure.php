<?php
/**
 * Framework Configure Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2006-2010 Satoshi Nishimura
 */

namespace laiz\core;

/**
 * Configuration of Laiz Framework class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Configure
{
    const BASE_CONFIG = 'config.ini';

    /** @var string */
    static private $projectDir;

    /** @var array */
    static private $configs;

    /**
     * setting directory of project
     */
    static public function setProjectDir($projectDir){
        self::$projectDir = $projectDir;
    }

    /**
     * read and save configure from ini file
     *
     * @access private
     */
    static private function initConfig(){
        // framework default
        $laizDir = dirname(dirname(__FILE__));
        $configs = parse_ini_file($laizDir . '/' . self::BASE_CONFIG, true);

        // user configure
        $fileName = self::$projectDir . self::BASE_CONFIG;
        if (file_exists($fileName)){
            $customs = parse_ini_file($fileName, true);
            foreach ($customs as $key => $value){
                // override
                if (isset($configs[$key]))
                    $configs[$key] = array_merge($configs[$key], $value);
            }
        }

        // put initial option ahead ini file setting
        $configs['base']['PROJECT_BASE_DIR'] = self::$projectDir;

        // replace value by ini special variables: {value}
        $configs = self::parseConfigValue($configs);
        self::$configs = $configs;
    }

    static private function parseConfigValue($configs)
    {
        $values = array();
        $newConfigs = array();
        foreach ($configs as $key => $value){
            foreach ($value as $k => $v){
                if (preg_match('/({[^}]+})/', $v, $matches)){
                    $argKey = trim($matches[1], '{}');
                    if (isset($values[$argKey])){
                        $replaced = str_replace($matches[1], $values[$argKey], $v);
                        $values[$k] = $replaced;
                        $newConfigs[$key][$k] = $replaced;
                    }else{
                        trigger_error('Undefined config value: ' . $matches[1], E_USER_WARNING);
                    }
                }else{
                    $values[$k] = $v;
                    $newConfigs[$key][$k] = $v;
                }
            }
        }

        return $newConfigs;
    }

    /**
     * get setting of framework and return class value
     *
     * @param string $className
     * @return array
     * @access private
     */
    static public function get($className = null){
        if (self::$configs === null){
            self::initConfig();
        }

        if ($className === null)
            $className = 'base';

        $className = str_replace('\\', '.', $className);
        return isset(self::$configs[$className]) ? self::$configs[$className] : array();
    }
}
