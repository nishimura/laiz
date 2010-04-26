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

/**
 * Default Action of Command Line.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Action_Default
{
    public $_version;
    public function act()
    {
        echo 'Laiz ', $this->_version;
        echo "\n";
        echo $this->commands();
        echo "\n";
    }

    private function commands()
    {
        $str = "COMMANDS: \n"
            . "  laiz.sh Help: View help.\n"
            . "  laiz.sh <ActionName>: Run action\n"
            ;
        return $str;
    }
}
