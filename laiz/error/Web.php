<?php
/**
 * Error Message Utility of Web Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\error;

/**
 * Error web class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Web extends Base
{
    /** @var Object singleton instance */
    static protected $instance;

    protected function init($args){
        $this->initLevel($args['LAIZ_ERROR_WEB_LEVEL']);
    }

    /**
     * show error
     *
     * @param string[] $backTrace
     * @access private
     */
    protected function _output($backTrace){
        $head = array_shift($backTrace);
        switch (true){
        case substr($head, 0, 8) === '[Notice]':
            $color = 'pink';
            break;
        case substr($head, 0, 9) === '[Warning]':
            $color = '#FF8888';
            break;
        default:
            $color = '#FF3333';
            break;
        }

        echo '<table cellpadding=4 cellspacing=0 style="border: 1px solid #999999; width:100%" border="1px; font-size: small;">';
        echo "\n";
        echo "<tr><th colspan=2 style=\"background-color:$color;\">$head</th></tr>\n";
        foreach ($backTrace as $b){
            list($func, $body) = explode(': in', $b);
            echo "<tr><td>$func</td><td>$body</td></tr>\n";
        }
        echo "</table>\n";
    }

}

