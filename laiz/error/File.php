<?php
/**
 * Error Message Utility of File Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2012 Satoshi Nishimura
 */

namespace laiz\error;

/**
 * Error file class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class File extends Base
{
    /** @var Object singleton instance */
    static protected $instance;

    /** @var string log directory */
    private $dir;
    /** @var bool */
    private $trace;

    protected function init($args){
        $this->dir = $args['ERROR_LOG_DIR'];
        $this->trace = $args['ERROR_LOG_TRACE'];

        $this->initLevel($args['LAIZ_ERROR_FILE_LEVEL']);
    }
    
    /**
     * write log file
     *
     * @param string[] $backTrace
     * @access protected
     */
    protected function _output($backTrace){
        $date = date('Y-m-d H:i:s');
        $head = array_shift($backTrace);
        $msg = "$date $head\n";

        if ($this->trace){
            // add all trace
            foreach ($backTrace as $b){
                $msg .= "  $b\n";
            }
        }
        
        error_log($msg, 3, $this->dir . date('Y-m-d') . '.log');
    }
}
