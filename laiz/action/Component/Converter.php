<?php
/**
 * Class to call converter.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\action;

use \laiz\parser\Ini_Simple;
use \laiz\action\Request;
use \laiz\command\Help;
use \laiz\lib\aggregate\laiz\action\Converters;

/**
 * Class to call converter.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  10
 */
class Component_Converter implements Component, Help
{
    /** @var Laiz_Parser */
    private $parser;

    /** @var ArrayObject */
    private $converters;

    /** @var array */
    private $converted = array();

    public function __construct(Ini_Simple $parser, Converters $converters
                                , Request $req)
    {
        $this->parser = $parser;
        $this->converters = $converters;
        $this->request = $req;
    }

    public function run(Array $config)
    {
        array_walk($config, array($this, 'runLine'));
    }

    private function runLine($value, $key)
    {
        if (in_array($key . $value, $this->converted))
            return;
        $this->converted[] = $key . $value;

        foreach (explode('|', $value) as $item){
            $item = trim($item);
            if (strlen($item) === 0)
                continue;
            $args = explode(',', $item);
            $funcName = trim(array_shift($args));

            $hit = false;
            foreach ($this->converters as $converter){
                if (!method_exists($converter, $funcName))
                    continue;

                $args = array_map('trim', $args);
                $var = $this->request->get($key);
                array_unshift($args, $var);
                $converted =
                    call_user_func_array(array($converter, $funcName), $args);
                $this->request->add($key, $converted);
                $hit = true;
            }
            if (!$hit)
                trigger_error("Not found $funcName converter.", E_USER_WARNING);
        }
    }

    public function help()
    {
        $docFile = str_replace('\\', '/', __CLASS__);
        $docFile = str_replace('_', '/', $docFile) . '.md';
        $ret = file_get_contents('doc/' . $docFile, FILE_USE_INCLUDE_PATH);

        $ret .= "\nConverter List\n-------------\n\n";
        foreach ($this->converters as $converter){
            $methods = get_class_methods($converter);
            foreach ($methods as $method){
                $ret .= '    ' . $method
                    . " 	in " . get_class($converter) . "\n";
            }
        }
        return $ret;
    }
}
