<?php
/**
 * Error Message Utility Creator Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 */


/**
 * エラー関連処理クラス生成クラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Error_Creator
{
    private static $instance;

    /**
     * @var Object[] エラー処理実装クラスの配列
     * @access private
     */
    private $_e = array();

    private $configs;
    /**
     * 外部からのnewの禁止
     *
     * @access private
     */
    private function __construct(){
        $conf = Laiz_Configure::get(__CLASS__);
        $this->configs = $conf;
    }

    /**
     * cloneの禁止
     *
     * @access public
     */
    public function __clone(){
        trigger_error('Clone is not allowed', E_ERROR);
    }
    
    /**
     * 定義済変数からサブクラスを設定する
     *
     * @access public
     */
    public function init(){
        if ($this->configs['LAIZ_ERROR_FILE_LEVEL']){
            $this->_e[] = $this->_getErrorUtils('File');
        }

        if ($this->configs['LAIZ_ERROR_MAIL_LEVEL']){
            $this->_e[] = $this->_getErrorUtils('Mail');
        }

        if ($this->configs['LAIZ_ERROR_SYSLOG_LEVEL']){
            $this->_e[] = $this->_getErrorUtils('Syslog');
        }
        
        if ($this->configs['LAIZ_ERROR_WEB_LEVEL']){
            $this->_e[] = $this->_getErrorUtils('Web');
        }
    }

    /**
     * LaizErrorUtilsサブクラスを返却
     *
     * @param string 'Web'|'Text'|'Mail'|'Syslog'
     * @return Object
     * @access private
     */
    private function _getErrorUtils($class){
        $className = "Laiz_Error_$class";

        if (class_exists($className))
            return call_user_func(array($className, 'getInstance'), $this->configs);

    }

    /**
     * 唯一のインスタンスを返却
     *
     * @return Object
     * @access public
     */
    static public function getInstance(){
        if (self::$instance === null){
            $c = __CLASS__;
            self::$instance = new $c();
            self::$instance->init();
        }

        return self::$instance;
    }

    /**
     * サブクラス Error 処理を実行してスクリプト終了
     *
     * @param string $msg
     * @access public
     */
    public function error($msg){
        foreach ($this->_e as $e){
            $e->error(E_USER_ERROR, $msg);
        }

        // エラーログを全て出力してからスクリプトを停止
        die();
    }

    /**
     * サブクラスの Warning 処理を実行
     *
     * @param string $msg
     * @access public
     */
    public function warning($msg){
        foreach ($this->_e as $e){
            $e->error(E_USER_WARNING, $msg);
        }
    }

    /**
     * サブクラスの Notice 処理を実行
     *
     * @param string $msg
     * @access public
     */
    public function notice($msg){
        foreach ($this->_e as $e){
            $e->error(E_USER_NOTICE, $msg);
        }
    }

    /**
     * ユーザエラー処理関数
     *
     * @param int $level エラーレベル
     * @param string $msg エラーメッセージ
     * @param string $file 標準エラーから呼ばれたときのエラーファイル名
     * @param string $line 標準エラーから呼ばれたときのエラー行数
     * @param string $text 標準エラーから呼ばれたときのメッセージ
     * @access public
     */
    static public function laizError($level, $msg, $file=null, $line=null, $text=null){
        // @(エラー制御演算子)がついている場合はerror_reportingの値がゼロになる
        $rep = error_reporting();

        $e = Laiz_Error_Creator::getInstance();

        $level = $level & $rep;
        if ($level === 0)
            return true; // 制御レベル0ならエラーにしない

        $error   = E_ERROR | E_USER_ERROR;
        $warning = E_WARNING | E_USER_WARNING;
        $notice  = E_NOTICE | E_USER_NOTICE;
        $strict  = E_STRICT;

        switch (true){
        case ($level & $error):
            $e->error($msg);
            break;

        case ($level & $warning):
            $e->warning($msg);
            break;

        case ($level & $notice):
            $e->notice($msg);
            break;

        default:
            // キャッチできなかったエラーは標準エラーに引き継ぎ
            return false;
            break;
        }

        return true;
    }
}

// 標準のエラーハンドラを書き換える
set_error_handler(array('Laiz_Error_Creator', 'laizError'));

error_reporting(E_ALL | E_STRICT);
