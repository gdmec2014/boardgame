<?php
/**
 * Created by PhpStorm.
 * User: Sinkr
 * Date: 2021/4/5
 * Time: 20:51
 */

namespace app\listener\game;

use app\listener\BaseEvent;
use Swoole\Server;
use think\Container;
use think\Event;
use think\swoole\Websocket;

class Test extends BaseEvent
{
    public function __construct(Server $server, Websocket $websocket, Container $container, Event $event)
    {
        parent::__construct($server, $websocket, $container, $event);
    }

    public function handle($event)
    {
        $this->server->push($this->websocket->getSender(), $this->assemblyData('test', ['success' => true], 200));
    }

}