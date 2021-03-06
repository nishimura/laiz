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
use \laiz\action\Validator_Simple;
use \laiz\lib\action\Response;
use \laiz\action\Result;

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

    /** @var laiz\lib\action\Response */
    private $response;

    public function generateResponse()
    {
        $this->response = new Response();
        return $this->response;
    }

    public function setHeader($type, $value)
    {
        $this->headers[$type] = "$type: $value";
    }

    /**
     * Validatorを使ったメールアドレスチェックとヘッダの設定
     *
     * @param string $type To,From,etc
     * @param string $mail
     * @param string $title メールアドレスの表示名
     * @return bool
     */
    public function setMailHeader($type, $mail, $title = null){
        $validator = new Validator_Simple();
        if (!$validator->mail($mail))
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
    public function setParam($param){
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
        return $this->setMailHeader('From', $mail, $title);
    }

    /**
     * 返信先ヘッダの設定
     *
     * @param string $mail
     * @return bool
     */
    public function setReplyTo($mail, $title = null){
        return $this->setMailHeader('Reply-To', $mail, $title);
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
    public function send($to, $subject, $message = null){
        if ($message === null && $this->response instanceof Response){
            $view = new Flexy();
            $view->setTemplateExtension('.txt');
            $view->setFlexyOptions(array('nonHTML' => true));
            $message = $view->bufferedOutput($this->response);
        }else if (!is_string($message)){
            trigger_error('Invalid argument of message.', E_USER_WARNING);
            return false;
        }

        // To ヘッダの設定
        if (is_array($to) && isset($to[0], $to[1]))
            $to = mb_encode_mimeheader($to[1], self::CHARSET) . ' <' . $to[0] . '>';

        // エンコーディング設定
        if (!isset($this->headers['Content-Type']))
            $this->headers['Content-Type'] = 'Content-Type: text/plain; charset=ISO-2022-JP';
        if (!isset($this->headers['Content-Transfer-Encoding']))
            $this->headers['Content-Transfer-Encoding']
                = 'Content-Transfer-Encoding: 7bit';

        $headers = implode("\n", $this->headers);
        $params  = implode(' ', $this->params);


        // メールの送信
        $ret = $this->runSend($to, $subject, $message, $headers, $params);
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

        return $this->runSend($to, $subject, $message, $headers, $params);
    }

    protected function runSend($to, $subject, $message, $headers, $params)
    {
        return mb_send_mail($to, $subject, $message, $headers, $params);
    }
}
