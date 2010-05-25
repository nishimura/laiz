<?php
/**
 * Mock of Mail Class.
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2010 Satoshi Nishimura
 */

namespace laiz\lib;

/**
 * Mock of Mail Class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Mail_Mock extends Mail
{
    private $data = array();

    protected function runSend($to, $subject, $message, $headers, $params)
    {
        $headers =
            'To: ' . $to . "\n"
            . $headers
            . 'Subject: ' . $subject . "\n";
        $this->data[] = $headers . "\n" . $message;
        return true;
    }

    public function getSentData()
    {
        return $this->data;
    }
}
