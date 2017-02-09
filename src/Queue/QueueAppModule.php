<?php
/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 13:03
 */

namespace Xaircraft\Queue;


use Xaircraft\DI;
use Xaircraft\Module\AppModule;

class QueueAppModule extends AppModule
{

    public function appStart()
    {
        DI::bindSingleton(QueueContext::class);
        /** @var QueueContext $context */
        $context = DI::get(QueueContext::class);
        DI::bindSingleton(BaseQueue::class, $context->getImplement());
    }

    public function handle()
    {
        // TODO: Implement handle() method.
    }

    public function appEnd()
    {
        TaskQueue::commit();
    }
}