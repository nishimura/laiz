<?php
/**
 * Error Message Utility of File Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * ファイル用エラー関連処理クラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Error_File extends Laiz_Error
{
    /** @var Object singleton instance */
    static protected $instance;

    /** @var string log directory */
    private $ERROR_LOG_DIR;
    /** @var bool */
    private $ERROR_LOG_TRACE;
    /** @var int error level */
    private $LAIZ_ERROR_FILE_LEVEL;
    /**
     * @access private
     */
    private function __construct($args){
        $this->ERROR_LOG_DIR = $args['ERROR_LOG_DIR'];
        $this->ERROR_LOG_TRACE = $args['ERROR_LOG_TRACE'];
        $this->LAIZ_ERROR_FILE_LEVEL = $args['LAIZ_ERROR_FILE_LEVEL'];
    }
    
    /**
     * インスタンスを返却
     *
     * @return LaizErrorUtilsMail
     * @access public
     */
    static public function getInstance($args = array()){
        if (self::$instance === null){
            $c = __CLASS__;
            self::$instance = new $c($args);
            self::$instance->_init();
        }

        return self::$instance;
    }
    
    /**
     * ログレベルの設定
     *
     * @access private
     */
    private function _init(){
        parent::init($this->LAIZ_ERROR_FILE_LEVEL);
    }

    /**
     * エラーをファイルに出力
     *
     * @param string[] $backTrace
     * @access protected
     */
    protected function _output($backTrace){
        $date = date('Y-m-d H:i:s');
        $head = array_shift($backTrace);
        $msg = "$date $head\n";

        if ($this->ERROR_LOG_TRACE){
            // バックトレース結果の追加
            foreach ($backTrace as $b){
                $msg .= "  $b\n";
            }
        }
        
        error_log($msg, 3, $this->ERROR_LOG_DIR . date('Y-m-d') . '.log');
    }
    
}

