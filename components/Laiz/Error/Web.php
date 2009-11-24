<?php
/**
 * Error Message Utility of Web Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 */

/**
 * Web用エラー関連処理クラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Error_Web extends Laiz_Error
{
    /** @var Object singleton instance */
    static protected $instance;

    /** @var int error level */
    private $LAIZ_ERROR_WEB_LEVEL;
    /**
     * 他のクラスからはnewしないこと
     *
     * @access private
     */
    private function __construct($args){
        $this->LAIZ_ERROR_WEB_LEVEL = $args['LAIZ_ERROR_WEB_LEVEL'];
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
     */
    private function _init(){
        parent::init($this->LAIZ_ERROR_WEB_LEVEL);
    }
    
    /**
     * エラー発生
     *
     * @param string $msg
     * @access public
     */
    public function error($level, $msg){
        if ($this->level & $level){
            $backTrace = $this->_getFormatBackTrace($this->levelTag[$level], $msg);
            $this->_output($backTrace);
        }
    }

    /**
     * エラーを画面に出力
     *
     * @param string[] $backTrace
     * @access private
     */
    private function _output($backTrace){
        echo '<table cellpadding=4 cellspacing=0 style="border: 1px solid #999999; width:100%" border="1px">';
        echo "\n";
        $head = array_shift($backTrace);
        echo "<tr><th colspan=2 style=\"background-color:pink;\">$head</th></tr>\n";
        foreach ($backTrace as $b){
            list($func, $body) = explode(': in', $b);
            echo "<tr><td>$func</td><td>$body</td></tr>\n";
        }
        echo "</table>\n";
    }

}

