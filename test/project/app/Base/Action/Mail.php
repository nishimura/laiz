<?php

use laiz\lib\Mail;

class Base_Action_Mail
{
    public $message;
    public function act(Mail $mail)
    {
        $mail->setFrom('nishim314@gmail.com', '送信者');
        // ...
    }
}
