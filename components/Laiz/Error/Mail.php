<?php
/**
 * Error Message Utility of Mail Class File
 *
 * PHP versions 5
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2009 Satoshi Nishimura
 */

/**
 * メール用エラー関連処理クラス
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Laiz_Error_Mail extends Laiz_Error
{
    /** @var Object singleton instance */
    static protected $instance;

    /** @var int error level */
    private $LAIZ_ERROR_MAIL_LEVEL;
    /** @var string mail address */
    private $ERROR_LOG_MAIL;
    /** @var string mail address */
    private $ERROR_LOG_MAIL_FROM;

    /**
     * @access private
     */
    private function __construct($args){
        $this->LAIZ_ERROR_MAIL_LEVEL = $args['LAIZ_ERROR_MAIL_LEVEL'];
        $this->ERROR_LOG_MAIL        = $args['ERROR_LOG_MAIL'];
        $this->ERROR_LOG_MAIL_FROM   = $args['ERROR_LOG_MAIL_FROM'];
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
        parent::init($this->LAIZ_ERROR_MAIL_LEVEL);
    }

    /**
     * メール送信処理
     *
     * @param string[] $backTrace
     * @access private
     */
    protected function _output($backTrace){
        $head = array_shift($backTrace);
        $msg = implode("\n", $backTrace);

        // ヘッダをタイトルにするには長すぎるので
        $msg = $head . "\n\n" . $msg;
        $head = "PHP ERROR MAIL";

        // 差出人設定
        $params = '-f' . $this->ERROR_LOG_MAIL_FROM;
        $headers = 'From: ' . $this->ERROR_LOG_MAIL_FROM;
        // エンコーディング設定
        $headers .= "\nContent-Type: text/plain; charset=ISO-2022-JP";
        $headers .= "\nContent-Transfer-Encoding: 7bit";

        // リクエスト情報を付加
        $msg .= "\n\n" . var_export($_REQUEST, true);

        foreach (explode(',', $this->ERROR_LOG_MAIL) as $mail){
            mb_send_mail(trim($mail), $head, $msg, $headers, $params);
        }
    }
    
}
