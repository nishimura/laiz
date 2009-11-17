<?php
/**
 * Framework Configure Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2006-2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Configuration of Laiz Framework class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Configure
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
        $configs = parse_ini_file(dirname(__FILE__) . '/' . self::BASE_CONFIG, true);

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
        $patterns = array();
        $replaces = array();
        foreach ($configs as $key => $value){
            foreach ($value as $k => $v){
                $patterns[] = '/' . preg_quote('{'.$k.'}', '/') . '/';
                $replaces[] = $v;
            }
        }
        foreach ($configs as $key => $value){
            $configs[$key] = preg_replace($patterns, $replaces, $value);
        }

        self::$configs = $configs;
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

        return isset(self::$configs[$className]) ? self::$configs[$className] : array();
    }
}
