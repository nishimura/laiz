<?php
/**
 * Error Message Utility of Syslog Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\error;

/**
 * syslog用エラー関連処理クラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Syslog extends Base
{
    /** @var Object singleton instance */
    static protected $instance;

    /** @var int error level */
    private $LAIZ_ERROR_SYSLOG_LEVEL;

    protected function init($args){
        $this->LAIZ_ERROR_SYSLOG_LEVEL = $args['LAIZ_ERROR_SYSLOG_LEVEL'];

        $this->initLevel($this->LAIZ_ERROR_SYSLOG_LEVEL);
    }
    
    static public function getInstance($args = array()){
        if (self::$instance === null){
            $c = __CLASS__;
            self::$instance = new $c($args);
            self::$instance->_init();
        }

        return self::$instance;
    }
    
    private function _init(){
        parent::init($this->LAIZ_ERROR_SYSLOG_LEVEL);
    }

    protected function _output($backTrace){
        $head = array_shift($backTrace);
        //$msg = implode("\n", $backTrace);

        syslog(LOG_INFO, "$head\n");
    }
    
}

