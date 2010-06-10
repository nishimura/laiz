<?php

use laiz\lib\Mail;

class Base_Action_Mail
{
    public $message;
    public $mail;
    public function act(Mail $mail)
    {
        if (!$this->mail)
            return;

        // this library to send mail in Japanese

        // set envelope from and From header.
        $mail->setFrom($this->mail, 'Laiz Framework');

        // change from header (optional)
        $mail->setMailHeader('From', $this->mail, 'Override');

        // set Reply-To header (optional)
        $mail->setReplyTo($this->mail, 'reply addr');

        // custom header
        $mail->setHeader('X-Laiz-Framework', 'Test');

        // send with string
        if ($mail->send($this->mail, 'Test Mail 1', "body\nmessages."))
            $this->message = '1: Success.';
        else
            $this->message = '1: Error.';

        // send with template
        $obj = new StdClass();
        $obj->prop1 = 'foo';
        $mail->generateResponse()
            ->setTemplateDir('Mail/templates/')
            ->setTemplateName('mail')
            ->addObject($obj);
        if ($mail->send($this->mail, 'Test Mail 2'))
            $this->message .= ' 2: Success.';
        else
            $this->message .= ' 2: Error.';
    }
}
