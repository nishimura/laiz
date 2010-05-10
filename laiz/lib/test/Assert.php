<?php
/**
 * Assert Utility for Testing.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib\test;

use \laiz\core\Configure;

/**
 * Assert Utility for Testing.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Assert
{
    const VIEW_SUCCESS = 1;
    const VIEW_FAILURE = 2;
    const VIEW_ALL     = 3;
    const VIEW_VERBOSE = 4;

    private $mode;
    private $failureCount = 0;
    private $successCount = 0;

    public function __construct($mode = self::VIEW_FAILURE)
    {
        $this->mode = $mode;
    }

    private function toString($value)
    {
        if (is_object($value))
            return get_class($value);
        if (is_array($value))
            return 'Array(' . count($value) . ')';
        return var_export($value, true);
    }

    public function equal($a, $b, $msg = null)
    {
        if ($msg === null)
            $msg = $this->toString($a) . ' === ' . $this->toString($b);

        if ($a === $b)
            $this->success($msg);
        else
            $this->failure($msg);
    }

    public function numeric($a, $msg = null)
    {
        if ($msg === null)
            $msg = $this->toString($a) . ' is numeric';

        if (is_numeric($a))
            $this->success($msg);
        else
            $this->failure($msg);
    }





    private function getLine()
    {
        $lines = debug_backtrace();
        $pre = null;
        $testClass = 'laiz\lib\test';
        $hit = array();
        foreach ($lines as $line){
            if (isset($line['class']) &&
                preg_match('/^'.preg_quote($testClass).'/', $line['class'])){
                $pre = $line;
                continue;
            }

            $hit = $line;
            break;
        }

        if ($this->mode & self::VIEW_VERBOSE)
            $file = $pre['file'];
        else
            $file = basename($pre['file']);

        $ret = array('file' => $file,
                     'line' => $pre['line'],
                     'function' => $hit['function'],
                     'class' => str_replace('\\', '.', $hit['class']),
                     'assert' => str_replace('\\', '.', $pre['function']));
        return $ret;
    }

    private function failure($msg)
    {
        $this->failureCount++;
        $line = $this->getLine();
        $msg = $this->decorate('Failure! ', 'red')
            . ' in ' . $line['file']
            . ' line ' . $line['line']
            . ', ' . $line['class']
            . '#' . $line['function']
            . $line['assert'] . ', (' . escapeshellcmd($msg) . ') '
            ;

        if ($this->mode & self::VIEW_FAILURE)
            echo `echo -e '$msg'`;
        return $this;
    }

    private function success($msg)
    {
        $this->successCount++;

        if ($this->mode & self::VIEW_SUCCESS){
            $line = $this->getLine();
            $msg = $this->decorate('Success! ', 'green')
                . ' in ' . $line['file']
                . ' line ' . $line['line']
                . ', (' . escapeshellcmd($msg) . ')'
                ;

            echo `echo -e '$msg'`;
        }
        return $this;
    }

    public function getFailureCount()
    {
        return $this->failureCount;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    private function decorate($str, $color)
    {
        $colors = array('bold'    => 1,
                        'white'   => 37,
                        'red'     => 31,
                        'green'   => 32,
                        'bgred'   => 41,
                        'bggreen' => 42,
                        );
        if (!is_array($color) && !isset($colors[$color]))
            return $str;

        if (is_array($color)){
            $patterns = array();
            foreach ($color as $c)
                $patterns[] = $colors[$c];
            $pattern = implode(';', $patterns);
        }else{
            $pattern = $colors[$color];
        }

        return '\e[' . $pattern . 'm' . $str . '\e[m';
                        
    }
    public function showResult()
    {
        $s = $this->getSuccessCount();
        $f = $this->getFailureCount();
        $t = $s + $f;
        if ($f)
            $bg = 'bgred';
        else
            $bg = 'bggreen';
        $str = "Success: $s"
            . ', '
            . "Failure: $f"
            . ', '
            . "Total: $t ";
        $str = $this->decorate($str, array($bg, 'white', 'bold'));
        echo `echo -e '$str'`;
    }
}
