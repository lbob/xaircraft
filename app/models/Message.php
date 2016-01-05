<?php

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/15
 * Time: 17:09
 */
class Message
{
    public $id = 1;

    public $content = "Hello message.";

    /**
     * @var Test\Hello\Contract
     */
    public $contract;

    public $closure;

    /**
     * @var \Test\Hello\Hello[]
     */
    public $hellos;

    private $emailSender;

    public function __construct(EmailSender $emailSender)
    {
        $this->emailSender = $emailSender;

        var_dump("I'm Message.");
    }

    public function sendEmail($to)
    {
        $this->emailSender->send($to, $this->content);
    }

    public function setClosure($closure)
    {
        $this->closure = $closure;
    }
}