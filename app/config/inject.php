<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2015/11/13
 * Time: 17:46
 */

use Xaircraft\DI;
use Xaircraft\Web\Session\FileSessionProvider;
use Xaircraft\Web\Session\SessionProvider;

DI::bindSingleton(SessionProvider::class, new FileSessionProvider());

DI::bindSingleton(EmailSender::class, EmailSenderImpl::class);
