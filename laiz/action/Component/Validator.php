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
use \laiz\core\Configure;

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

    private $handleByMethod;
    public function __construct(Validators $validators, Ini $parser
                                , Request $req, Validator_Result $res)
    {
        $this->validators = $validators;
        $this->parser  = $parser;
        $this->request = $req;
        $this->result  = $res;

        $config = Configure::get('laiz.action.Validator');
        $this->handleByMethod = (boolean) $config['handleByMethod'];
    }

    // ==TODO== refactoring
    public function run(Array $config)
    {
        if (!isset($config['file'])){
            trigger_error('file section are required in ini config file.');
            return;
        }
        if (!$this->handleByMethod && !isset($config['errorAction'])){
            trigger_error('errorAction section are required in ini config file.');
            return;
        }
        if ($this->handleByMethod && !isset($config['trigger'])){
            trigger_error('trigger section are required in ini config file.');
            return;
        }

        if (isset($config['trigger'])){
            if (!$this->getRequestValue($config['trigger'])){
                return;
            }
            if (isset($this->result->_validatorChecked) &&
                $this->result->_validatorChecked)
                return;
            $this->result->_validatorChecked = true;
        }
        if (isset($config['errorKeyPrefix']))
            $prefix = $config['errorKeyPrefix'];
        else
            $prefix = self::ERROR_KEY_PREFIX;

        if (isset($config['stop']) && $config['stop'])
            $defaultStop = true;
        else
            $defaultStop = false;

        $invalid = false;
        $data = $this->parser->parse($config['file']);
        if (!$data){
            trigger_error('ini file parsing failed: ' . $config['file'],
                          E_USER_WARNING);
            if ($this->handleByMethod)
                return;
            else
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
                $empty = true;
                $args = array($this->getRequestValue($argName));
                if ($args[0] !== null &&
                    trim($args[0]) !== '')
                    $empty = false;
                if ($argsStr){
                    // arguments: ex. "(3, 4)"
                    $a = trim($argsStr, '()');
                    $a = explode(',', $a);
                    foreach ($a as $v){
                        $v = trim($v);
                        if ($v[0] === '$'){
                            $v = $this->getRequestValue(ltrim($v, '$'));
                            if ($v !== null && trim($v) !== '')
                                $empty = false;
                        }
                        $args[] = $v;
                    }
                }
                if (strpos($method, 'required') !== 0 && $empty){
                    continue;
                }

                $ok = call_user_func_array($callback, $args);
                if (!$ok){
                    $invalid = true;
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

            if ($invalid && $stop)
                break;
        }

        if ($invalid){
            $this->result->_success = false;
            if (isset($config['errorMessage'], $config['errorMessageKey']))
                $this->result->{$config['errorMessageKey']} = $config['errorMessage'];
            if ($this->handleByMethod)
                return;
            else
                return 'action:' . $config['errorAction'];
        }else{
            $this->result->_success = true;
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
