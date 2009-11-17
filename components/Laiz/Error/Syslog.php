<?php
/**
 * Error Message Utility of Syslog Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * syslog用エラー関連処理クラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Error_Syslog extends Laiz_Error
{
    /** @var Object singleton instance */
    static protected $instance;

    /** @var int error level */
    private $LAIZ_ERROR_SYSLOG_LEVEL;
    /**
     * 他のクラスからはnewしないこと
     *
     * @access private
     */
    private function __construct($args){
        $this->LAIZ_ERROR_SYSLOG_LEVEL = $args['LAIZ_ERROR_SYSLOG_LEVEL'];
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

