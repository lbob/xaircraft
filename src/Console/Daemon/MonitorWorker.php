<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16/4/19
 * Time: 15:46
 */

namespace Xaircraft\Console\Daemon;


use Xaircraft\Exception\ExceptionHelper;

class MonitorWorker extends Worker
{
    protected $port;

    public function __construct(array $args, $port)
    {
        parent::__construct($args);

        ExceptionHelper::ThrowIfNotTrue($port >= 0 && $port < 65535, '端口号错误');

        $this->port = $port;
    }

    public function onWorkerProcess()
    {
        if ($this->port > 0) {
            declare (ticks = 2) {
                $server = 'udp://127.0.0.1:' . $this->port;
                $msg_eof = "\n";
                $socket = stream_socket_server($server, $errno, $errstr, STREAM_SERVER_BIND);
                if (!$socket) {
                    $this->log("$errstr ($errno)");
                    $this->stopAll(0);
                } else {
                    $this->log("MonitorWorker listen " . $this->port);
                }

                do {
                    $inMsg = stream_socket_recvfrom($socket, 1024, 0, $peer);
                    $this->log("Client : $peer\n");
                    $this->log("Receive : {$inMsg}");
                    $outMsg = substr($inMsg, 0, (strrpos($inMsg, $msg_eof)));
                    stream_socket_sendto($socket, $outMsg, 0, $peer);
                } while (1);
            }
        }
    }

    public function getWorkerName()
    {
        return "monitor";
    }

    public function getWorkerRestartLimit()
    {
        return 3;
    }
}