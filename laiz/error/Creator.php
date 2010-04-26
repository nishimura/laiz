<?php
/**
 * Error Message Utility Creator Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2010 Satoshi Nishimura
 */

namespace laiz\error;

use \laiz\core\Configure;

/**
 * エラー関連処理クラス生成クラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Creator
{
    private static $instance;

    /**
     * @var Object[] エラー処理実装クラスの配列
     * @access private
     */
    private $objs = array();

    private $configs;
    /**
     * 外部からのnewの禁止
     *
     * @access private
     */
    private function __construct(){
        $conf = Configure::get(__CLASS__);
        $this->configs = $conf;
    }

    /**
     * not allow clone
     */
    private function __clone(){
        trigger_error('Clone is not allowed', E_ERROR);
    }

    /**
     * override default error handler
     */
    public static function register()
    {
        set_error_handler(array('laiz\error\Creator', 'laizError'));
        error_reporting(E_ALL | E_STRICT);
    }

    /**
     * 定義済変数からサブクラスを設定する
     *
     * @access public
     */
    public function init(){
        if ($this->configs['LAIZ_ERROR_FILE_LEVEL']){
            $this->objs[] = File::getInstance($this->configs);
        }

        if ($this->configs['LAIZ_ERROR_MAIL_LEVEL']){
            $this->objs[] = Mail::getInstance($this->configs);
        }

        if ($this->configs['LAIZ_ERROR_SYSLOG_LEVEL']){
            $this->objs[] = Syslog::getInstance($this->configs);
        }
        
        if ($this->configs['LAIZ_ERROR_WEB_LEVEL']){
            $this->objs[] = Web::getInstance($this->configs);
        }
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
        foreach ($this->objs as $e){
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
        foreach ($this->objs as $e){
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
        foreach ($this->objs as $e){
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

        $e = self::getInstance();

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
