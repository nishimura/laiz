<?php
/**
 * Error Message Utility of Syslog Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2012 Satoshi Nishimura
 */

namespace laiz\error;

/**
 * Error syslog class
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Syslog extends Base
{
    /** @var Object singleton instance */
    static protected $instance;

    protected function init($args){
        $this->initLevel($args['LAIZ_ERROR_SYSLOG_LEVEL']);
    }
    
    protected function _output($backTrace){
        $head = array_shift($backTrace);
        //$msg = implode("\n", $backTrace);

        syslog(LOG_INFO, "$head\n");
    }
}
