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

/**
 * View Help.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Action_Help implements Describable
{
    public function act()
    {
        echo "command list             : laiz.sh\n";
        echo "setting project base dir : BASE=path/to/dir/ laiz.sh action\n";
    }

    public function describe()
    {
        return 'View help.';
    }
}
