<?php

namespace Xaircraft\Queue;
use Xaircraft\Console\Console;

/**
 * Created by PhpStorm.
 * User: lbob
 * Date: 2017/2/9
 * Time: 10:59
 */
class QueueEvents
{
    const EVENT_ONSTART = 'onStart';

    public static function onStart(QueueContext $context)
    {
        self::emit(self::EVENT_ONSTART, $context);
    }

    const EVENT_ONRESUMED = 'onResumed';

    public static function onResumed(QueueContext $context)
    {
        self::emit(self::EVENT_ONRESUMED, $context);
    }

    const EVENT_ONSTOP = 'onStop';

    public static function onStop(QueueContext $context)
    {
        self::emit(self::EVENT_ONSTOP, $context);
    }

    const EVENT_ONTASKBEFOREFIRE = 'onTaskBeforeFire';

    public static function onTaskBeforeFire(QueueContext $context)
    {
        self::emit(self::EVENT_ONTASKBEFOREFIRE, $context);
    }

    const EVENT_ONTASKRESOLVED = 'onTaskResolved';

    public static function onTaskResolved(QueueContext $context)
    {
        self::emit(self::EVENT_ONTASKRESOLVED, $context);
    }

    const EVENT_ONTASKREJECTED = 'onTaskRejected';

    public static function onTaskRejected(QueueContext $context)
    {
        self::emit(self::EVENT_ONTASKREJECTED, $context);
    }

    const EVENT_ONTASKRESUME = 'onTaskResume';

    public static function onTaskResume(QueueContext $context)
    {
        self::emit(self::EVENT_ONTASKRESUME, $context);
    }

    const EVENT_ONERROR = 'onError';

    public static function onError(QueueContext $context, \Exception $ex)
    {
        self::emit(self::EVENT_ONERROR, $context);
    }

    private static function emit($eventname, QueueContext $context)
    {
        $event = $context->getConfig()->find('event')->find($eventname)->get();

        if (is_callable($event)) {
            try {
                call_user_func($event, $context);
            } catch (\Exception $ex) {
                if ($eventname !== self::EVENT_ONERROR) {
                    self::onError($context, $ex);
                } else {
                    throw $ex;
                }
            }
        }
    }
}