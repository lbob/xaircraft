<?php
/**
 * Created by PhpStorm.
 * User: skyweo
 * Date: 16/4/19
 * Time: 15:46
 */

namespace Xaircraft\Console\Daemon;


use Xaircraft\Core\Json;
use Xaircraft\Exception\ExceptionHelper;
use Xaircraft\Extensions\Log\Log;

class MonitorWorker extends Worker
{
    protected $port;

    public function __construct($watchWorkerName, array $args, $port)
    {
        $this->watchWorkerName = $watchWorkerName;
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

                    $pidFile = WorkerProcessContainer::getPidFile($this->watchWorkerName);
                    $this->log("PIDFILE:$pidFile");
                    if (file_exists($pidFile)) {
                        $content = file_get_contents($pidFile);
                        if (!posix_kill(intval($content), SIGUSR1)) {
                            $this->log("Can't SIGUSR1 to WorkerProcessContainer.");
                        } else {
                            $this->log("SIGUSR1 to WorkerProcessContainer success.");
                            sleep(3);
                            $path = WorkerProcessContainer::getWorkerInfoPath($this->watchWorkerName);
                            Log::debug('MonitorWorker::onWorkerProcess:', $path);
                            if (file_exists($path)) {
                                $content = file_get_contents($path);
                                if (!empty($content)) {
                                    /** @var WorkerStatus[] $workers */
                                    $workers = Json::toArray($content, WorkerStatus::class);

                                    if (!empty($workers)) {
                                        foreach ($workers as $worker) {
                                            if ($worker->name == $this->watchWorkerName) {
                                                if ($worker->status == Worker::STATUS_RUNNING || $worker->status == Worker::STATUS_STARTING) {
                                                    $outMsg = substr($inMsg, 0, (strrpos($inMsg, $msg_eof)));
                                                    stream_socket_sendto($socket, $outMsg, 0, $peer);
                                                    continue;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $outMsg = substr($inMsg, 0, (strrpos($inMsg, $msg_eof))) . "_error";
                    stream_socket_sendto($socket, $outMsg, 0, $peer);
                    sleep(5);

                } while (1);
            }
        }
    }

    public function getWorkerName()
    {
        return "monitor_" . $this->watchWorkerName;
    }

    public function getWorkerRestartLimit()
    {
        return 3;
    }
}