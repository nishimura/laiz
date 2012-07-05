<?php
/**
 * Error Message Utility of Mail Class File
 *
 * PHP versions 5.3
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 * @copyright 2005-2012 Satoshi Nishimura
 */

namespace laiz\error;

/**
 * Error mail class.
 *
 * @package   Laiz
 * @author    Satoshi Nishimura <nishim314@gmail.com>
 */
class Mail extends Base
{
    /** @var Object singleton instance */
    static protected $instance;

    /** @var string mail address */
    private $to;
    /** @var string mail address */
    private $from;
    /** @var string title */
    private $title;

    protected function init($args){
        $this->to    = $args['ERROR_LOG_MAIL'];
        $this->from  = $args['ERROR_LOG_MAIL_FROM'];
        $this->title = $args['ERROR_LOG_MAIL_TITLE'];

        $this->initLevel($args['LAIZ_ERROR_MAIL_LEVEL']);
    }

    /**
     * send mail
     *
     * @param string[] $backTrace
     * @access private
     */
    protected function _output($backTrace){
        $head = array_shift($backTrace);
        $msg = implode("\n", $backTrace);

        // set fixed title because header is too long
        $msg = $head . "\n\n" . $msg;
        $head = $this->title;

        // set envelope from
        $params = '-f' . $this->from;
        $headers = 'From: ' . $this->from;
        // set japanese encoding
        // TODO: setting in config.ini
        $headers .= "\nContent-Type: text/plain; charset=ISO-2022-JP";
        $headers .= "\nContent-Transfer-Encoding: 7bit";

        // additional information
        $msg .= "\n\n" . var_export($_REQUEST, true);

        foreach (explode(',', $this->to) as $mail){
            mb_send_mail(trim($mail), $head, $msg, $headers, $params);
        }
    }
    
}
