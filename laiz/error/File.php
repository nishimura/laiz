<?php
/**
 * Error Message Utility of File Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\error;

/**
 * ファイル用エラー関連処理クラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class File extends Base
{
    /** @var Object singleton instance */
    static protected $instance;

    /** @var string log directory */
    private $ERROR_LOG_DIR;
    /** @var bool */
    private $ERROR_LOG_TRACE;
    /** @var int error level */
    private $LAIZ_ERROR_FILE_LEVEL;

    protected function init($args){
        $this->ERROR_LOG_DIR = $args['ERROR_LOG_DIR'];
        $this->ERROR_LOG_TRACE = $args['ERROR_LOG_TRACE'];
        $this->LAIZ_ERROR_FILE_LEVEL = $args['LAIZ_ERROR_FILE_LEVEL'];

        $this->initLevel($this->LAIZ_ERROR_FILE_LEVEL);
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
