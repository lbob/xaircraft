<?php

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/12/16
 * Time: 20:16
 */
interface EmailSender
{
    public function send($to, $content);
}