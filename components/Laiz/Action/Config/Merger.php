<?php
/**
 * File of class for merging action configration.
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * Class for merging action configration.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Action_Config_Merger
{
    /** file of directory global configuration */
    const GLOBAL_CONFIG = 'laiz';

    /** @var Laiz_Parser */
    private $parser;

    public function __construct(Laiz_Parser $parser)
    {
        $this->parser = $parser;
    }

    public function merge($actionName)
    {
        $configFile = '/' . str_replace('_', '/', $actionName);
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
            if (isset($configs[$section]))
                $configs[$section] = array_merge($configs[$section], $value);
            else
                $configs[$section] = $value;
        }
        return $configs;
    }
}
