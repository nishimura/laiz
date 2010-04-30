<?php
/**
 * Default Action of Command Line.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\command;

use \laiz\lib\aggregate\laiz\command\Describables;

/**
 * Default Action of Command Line.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Action_Default
{
    public $h;
    public $help;
    public $arg1;
    public $_version;
    public function act(Describables $describables)
    {
        if ($this->h || $this->help){
            if ($this->arg1){
                $action = $this->arg1;
                return "action:$action";
            }else{
                return 'action:Help';
            }
        }
        echo 'Laiz ', $this->_version;
        echo "\n";

        echo "COMMANDS\n";
        foreach ($describables as $describe){
            $className = get_class($describe);
            $className = str_replace('laiz\\command\\Action_', '', $className);
            $className = sprintf("%-12s", $className);
            $className{0} = strtolower($className{0});
            echo '  laiz.sh ' . $className . ": " . $describe->describe();
            echo "\n";
        }
        echo "\n";
    }
}
