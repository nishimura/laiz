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
        if ($this->arg1){
            $arg = str_replace('.', '\\', $this->arg1);
        }else{
            $arg = null; // class name for help
        }

        $action = null; // action class of specified command
        $ret = '';      // return string if not specify
        foreach ($helps as $help){
            $className = get_class($help);
            /* if (!preg_match('/[a-zA-Z0-9_]+$/', get_class($help), $matches))
             *     continue; */
            if (($tmp = str_replace('laiz\\command\\Action_', '', $className))
                !== $className){
                // special short cut
                // laiz help cmd => laiz\command\Action_Cmd
                $className = $tmp;
                $className{0} = strtolower($className{0});
            }

            if ($arg && $className === $arg){
                $action = $help;
                break;
            }else{
                $command = $className;
                $command = str_replace('\\', '.', $command);
                $ret .= '  laiz.sh help ' . $command . "\n";
                    /* . sprintf('%-32s', $command)
                     * . ': '. $help->help() . "\n"; */
            }
        }

        if ($arg && $action){
            echo $action->help() . "\n";
            return;
        }else if ($arg){
            echo "Can not find $arg class.\n";
            return;
        }

        // default help
        echo "setting project base dir : BASE=path/to/dir/ laiz.sh command\n";
        echo "                      or : export BASE=path/to/dir\n";
        echo "                      or : export PROJECT_BASE_DIR=path/to/dir\n";
        echo "\n";
        echo "HELP COMMANDS\n";

        // each help of class
        echo $ret;
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
