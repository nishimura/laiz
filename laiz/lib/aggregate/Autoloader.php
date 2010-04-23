<?php
/**
 * Auto Creating Aggregate Object for Container.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\aggregate;

use \laiz\autoloader\Register;
use \laiz\util\Inflector;

/**
 * Auto Creating Aggregate Object for Container.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @priority  10
 */
class Autoloader implements Register
{
    public function autoload($name)
    {
        $pattern = preg_quote(__NAMESPACE__, '/');
        if (!preg_match("/^$pattern/", $name))
            return;

        $pattern = preg_quote('\\');
        if (!preg_match("/(${pattern}[^$pattern]+)$/", $name, $matches))
            return;

        $className = $matches[1];

        $fullNamespace = str_replace($className, '', $name);
        $realNamespace = str_replace(__NAMESPACE__ , '', $fullNamespace);

        $interface = $realNamespace . Inflector::singularize($className);
        $interface = ltrim($interface, '\\');

        $className = ltrim($className, '\\');

        // fullNamespace: laiz\lib\aggregate\path\to
        // realNamespace: \path\to
        // interface    : path\to\Object
        // className:     Objects

        // debug
        //var_dump($fullNamespace, $realNamespace, $interface, $className);

        eval("
namespace $fullNamespace;
use \\ArrayObject;
use \\laiz\\builder\\Container;
class $className extends ArrayObject
{
    public function __construct(\$input = null, \$flag = 0, \$iterator = 'ArrayIterator')
    {
        if (\$input === null){
            \$input = Container::getInstance()->getComponents('$interface');
        }
        parent::__construct(\$input, \$flag, \$iterator);
    }
}
");
    }
}
