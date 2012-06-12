<?php
/**
 * Class file of parsing validator section in setting file.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2009-2010 Satoshi Nishimura
 */

namespace laiz\action;

use \laiz\lib\aggregate\laiz\action\Validators;
use \laiz\parser\Ini;
use \laiz\command\Help;

/**
 * Class of parsing validator section in setting file.
 * 
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  20
 */
class Component_Validator implements Component, Help
{
    const ERROR_KEY_PREFIX = 'error';

    /** @var ArrayObject */
    private $validators;

    /** @var laiz\parser\Ini */
    private $parser;

    /** @var laiz\action\Request */
    private $request;

    /** @var laiz\action\Validator_Result */
    private $result;

    public function __construct(Validators $validators, Ini $parser
                                , Request $req, Validator_Result $res)
    {
        $this->validators = $validators;
        $this->parser  = $parser;
        $this->request = $req;
        $this->result  = $res;
    }

    // ==TODO== refactoring
    public function run(Array $config)
    {
        if (!isset($config['file'], $config['errorAction'])){
            trigger_error('file section and errorAction sections are required in ini config file.');
            return;
        }

        if (isset($config['trigger'])){
            if (!$this->getRequestValue($config['trigger'])){
                return;
            }
            if (isset($this->result->__validatorChecked) &&
                $this->result->__validatorChecked)
                return;
            $this->result->__validatorChecked = true;
        }
        if (isset($config['errorKeyPrefix']))
            $prefix = $config['errorKeyPrefix'];
        else
            $prefix = self::ERROR_KEY_PREFIX;

        if (isset($config['stop']) && $config['stop'])
            $defaultStop = true;
        else
            $defaultStop = false;

        $valid = false;
        $data = $this->parser->parse($config['file']);
        if (!$data){
            trigger_error('ini file parsing failed: ' . $config['file'],
                          E_USER_WARNING);
            return 'action:' . $config['errorAction'];
        }

        foreach ($data as $argName => $lines){
            if (isset($lines['stop']))
                $stop = $lines['stop'];
            else
                $stop = $defaultStop;

            foreach ($lines as $key => $value){
                if ($key === 'stop')
                    continue;

                preg_match('/^([^\(]+)(\([^\)]+\))?/', $key, $matches);
                if (!isset($matches[1])){
                    trigger_error("Invalid key of validator: $key.");
                    continue;
                }
                $method = $matches[1];
                if (isset($matches[2]))
                    $argsStr = $matches[2];
                else
                    $argsStr = null;
                $hit = false;
                foreach ($this->validators as $validator){
                    // search validator method
                    if (!method_exists($validator, $method))
                        continue;

                    $hit = true;
                    $callback = array($validator, $method);
                }
                if (!$hit)
                    trigger_error("Not found $method validator", E_USER_WARNING);
                $args = (array)$this->getRequestValue($argName);
                if ($argsStr){
                    // arguments: ex. "(3, 4)"
                    $a = trim($argsStr, '()');
                    $a = explode(',', $a);
                    foreach ($a as $v){
                        $v = trim($v);
                        if ($v[0] === '$')
                            $v = $this->getRequestValue(ltrim($v, '$'));
                        $args[] = $v;
                    }
                }

                $ok = call_user_func_array($callback, $args);
                if (!$ok){
                    $valid = true;
                    if (strpos($argName, '.') === false){
                        $errorKey = $prefix . ucfirst($argName);
                    }else{
                        $words = explode('.', $argName);
                        $words = array_map('ucfirst', $words);
                        $errorKey = $prefix . implode($words);
                    }
                    $this->result->$errorKey = $value;
                    break;
                }

            }

            if ($valid && $stop)
                break;
        }

        if ($valid){
            if (isset($config['errorMessage'], $config['errorMessageKey']))
                $this->result->{$config['errorMessageKey']} = $config['errorMessage'];
            return 'action:' . $config['errorAction'];
        }else{
            return;
        }
    }

    private function getRequestValue($key)
    {
        if (strpos($key, '.') === false)
            return $this->request->get($key);
        
        preg_match('/^([^.]+)\.(.+)/', $key, $matches);
        $arg = $this->request->get($matches[1]);
        if (is_object($arg))
            return $arg->{$matches[2]};
        else if (is_array($arg))
            return $arg[$matches[2]];
        else
            return null;
    }

    public function help()
    {
        $docFile = str_replace('\\', '/', __CLASS__);
        $docFile = str_replace('_', '/', $docFile) . '.md';
        $ret = file_get_contents('doc/' . $docFile, FILE_USE_INCLUDE_PATH);

        $ret .= "\nValidator List\n-------------\n\n";
        foreach ($this->validators as $validator){
            $methods = get_class_methods($validator);
            foreach ($methods as $method){
                if (preg_match('/^__/', $method))
                    continue;
                $ret .= '    ' . $method
                    . " 	in " . get_class($validator) . "\n";
            }
        }
        return $ret;
    }
}
