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

use laiz\parser\Ini_Simple;

/**
 * Class of filter component.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  80
 */
class Component_Filter implements Component
{
    /** @var Laiz_Parser */
    private $parser;

    public function __construct(Ini_Simple $parser)
    {
        $this->parser = $parser;
    }

    public function run(Array $config)
    {
        return $this->main($config, 'filter');
    }

    /**
     * @param $defaultMethod string
     */
    public function main(Array $config, $defaultMethod)
    {
        foreach ($config as $key => $val){
            if (strlen(trim($val)) === 0)
                continue;

            $method = $defaultMethod;

            if (strpos($val, '.')){
                list($class, $method) = explode('.', $val);
            }else{
                $class = $val;
            }

            $thisConfig = array();
            $iniFile = str_replace('_', '/', $class);
            $iniFile = str_replace('\\', '/', $iniFile);
            $cfg = $this->parser->parse($iniFile);
            if ($cfg === false)
                $cfg = array();

            $ret = Component_Runner::run($class, $cfg, $method);
            if ($ret)
                return $ret;
        }
    }
}
