<?php
/**
 * View Help.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\command;

use \laiz\lib\aggregate\laiz\command\Helps;

/**
 * View Help.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Action_Help implements Describable
{
    public $arg1;
    public function act(Helps $helps)
    {
        $action = null; // action class of specified command
        $ret = "  *** application ***\n";  // return string if not specify
        $arr = $this->parseHelps($helps);
        foreach ($arr['app'] as $name => $help){
            if ($this->arg1 &&  $name === $this->arg1){
                $action = $help;
                break;
            }else{
                if ($action->help()) // add to list if exists value
                    $ret .= '  laiz help ' . $name . "\n";
            }
        }

        $ret .= "\n  *** framework ***\n";
        foreach ($arr['laiz'] as $name => $help){
            if ($this->arg1 &&  $name === $this->arg1){
                $action = $help;
                break;
            }else{
                $ret .= '  laiz help ' . $name . "\n";
            }
        }

        if ($this->arg1 && $action){
            echo $action->help() . "\n";
            return;
        }else if ($this->arg1){
            echo "Can not find $this->arg1 class.\n";
            return;
        }

        // default help
        // each help of class
        echo "HELP COMMANDS\n";
        echo $ret;
    }

    private function parseHelps(Helps $helps)
    {
        $ret = array();
        $ret['laiz'] = array();
        $ret['app']  = array();
        foreach ($helps as $help){
            $fullName = get_class($help);
            $fullName = str_replace('\\', '.', $fullName);

            if (preg_match('/^' . preg_quote('laiz.') . '/', $fullName))
                $base = 'laiz';
            else
                $base = 'app';

            if (($cmd = str_replace('laiz.command.Action_', '', $fullName))
                !== $fullName){
                // special short name
                // laiz help cmd => laiz\command\Action_Cmd
                $cmd{0} = strtolower($cmd{0});
                if (!isset($ret[$base][$cmd])){
                    $ret[$base][$cmd] = $help;
                    continue;
                }
            }

            if (preg_match('/[a-zA-Z0-9_]+$/', $fullName, $matches)){
                // short name
                $shortName = $matches[0];
                if (!isset($ret[$base][$shortName])){
                    $ret[$base][$shortName] = $help;
                    continue;
                }
            }

            $ret[$base][$fullName] = $help;
        }
        return $ret;
    }

    public function describe()
    {
        return 'View help.';
    }

    public function help()
    {
        return 'This command.';
    }
}
