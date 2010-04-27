<?php
/**
 * File of Mail Sender Class for Japanese
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2007-2010 Satoshi Nishimura
 */

namespace laiz\lib;

use \laiz\view\Flexy;
use \laiz\validator\Simple as Validator;

/**
 * Mail Sender Class for Japanese
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright Copyright (c) 2007-2009 Satoshi Nishimura
 */
class Mail
{
    /** @var array */
    private $headers = array();

    /** @var array */
    private $params = array();

    /** @var Laiz_View */
    private $view;

    /** @var mixed */
    private $toCopy;

    /** ヘッダのエンコーディング */
    const CHARSET = 'ISO-2022-JP';

    /**
     * デフォルトのヘッダを設定する
     */
    public function __construct(Flexy $view){
        $this->view = clone $view;
    }

    /**
     * メールテンプレートのディレクトリを設定する
     *
     * @param string $dir 
     */
    public function setTemplateDir($dir)
    {
        $this->view->setTemplateDir($dir);
    }

    /**
     * Validatorを使ったメールアドレスチェックとヘッダの設定
     *
     * @param string $type To,From,etc
     * @param string $mail
     * @param string $title メールアドレスの表示名
     * @return bool
     */
    private function setHeader($type, $mail, $title = null){
        if (!Validator::isMail($mail))
            return false;

        if ($title)
            $mail = mb_encode_mimeheader($title, self::CHARSET) . " <$mail>";
        $this->headers[$type] = "$type: $mail";

        return true;
    }

    /**
     * sendmailのパラメータを設定する
     *
     * @param string $param
     */
    private function setParam($param){
        $this->params[$param] = $param; // 同じパラメータは上書きする
    }

    /**
     * エラーメール受信ヘッダの設定
     *
     * @param string $mail
     * @return bool
     */
    public function setEnvelopeFrom($mail){
        $this->setParam("-f$mail");
    }

    /**
     * 送信元ヘッダの設定
     *
     * @param string $mail
     * @return bool
     */
    public function setFrom($mail, $title = null){
        $this->setEnvelopeFrom($mail);
        return $this->setHeader('From', $mail, $title);
    }

    /**
     * 返信先ヘッダの設定
     *
     * @param string $mail
     * @return bool
     */
    public function setReplyTo($mail, $title = null){
        return $this->setHeader('Reply-To', $mail, $title);
    }

    /**
     * 管理者などにメールのコピーを送信する場合の管理者メールアドレスを設定する
     *
     * @param string $mail
     * @param string $title
     */
    public function setToCopy($mail, $title = null){
        if ($title)
            $this->toCopy = array($mail, $title);
        else
            $this->toCopy = $mail;
    }

    /**
     * テキストのメッセージを指定してメールを送信する
     *
     * @param string $to
     * @param string $subject
     * @param string $message
     * @return bool
     */
    public function send($to, $subject, $message){
        // To ヘッダの設定
        if (is_array($to) && isset($to[0], $to[1]))
            $to = mb_encode_mimeheader($to[1], self::CHARSET) . '<' . $to[0] . '>';

        // エンコーディング設定
        if (!isset($this->headers['Content-Type']))
            $this->headers['Content-Type'] = 'Content-Type: text/plain; charset=ISO-2022-JP';
        if (!isset($this->headers['Content-Transfer-Encoding']))
            $this->headers['Content-Transfer-Encoding']
                = 'Content-Transfer-Encoding: 7bit';

        $headers = implode("\n", $this->headers);
        $params  = implode(' ', $this->params);


        // メールの送信
        $ret = mb_send_mail($to, $subject, $message, $headers, $params);
        if (!$ret)
            return $ret;

        if (!$this->toCopy)
            return $ret;

        // 必要なら管理者へメールのコピーを送信
        if (is_array($this->toCopy) && isset($this->toCopy[0], $this->toCopy[1])){
            $to = mb_encode_mimeheader($this->toCopy[1], self::CHARSET) . '<' . $this->toCopy[0] . '>';
        }else{
            $to = $this->toCopy;
        }

        return mb_send_mail($to, $subject, $message, $headers, $params);
    }

    /**
     * テンプレートを指定してメールを送信する
     *
     * @param string $to
     * @param string $subject
     * @param string $template
     * @return bool
     */
    public function sendByTemplate($to, $subject, $template){
        $this->view->setTemplateExtension('.txt');
        $this->view->setFlexyOptions(array('nonHTML' => true));
        $body = $this->view->bufferedOutput($template);

        return $this->send($to, $subject, $body);
    }
}
