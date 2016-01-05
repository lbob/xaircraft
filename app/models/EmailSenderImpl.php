<?php

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/16
 * Time: 20:17
 */
class EmailSenderImpl implements EmailSender
{

    public function send($to, $content)
    {
        var_dump("Email send to [$to], content is [$content].");
    }
}