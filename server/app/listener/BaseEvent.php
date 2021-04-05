<?php
/**
 * Created by PhpStorm.
 * User: Sinkr
 * Date: 2021/4/5
 * Time: 21:02
 */

namespace app\listener;

use Swoole\Server;
use think\Container;
use think\swoole\Table;
use think\swoole\Websocket;
use think\Event;

class BaseEvent
{
    protected $websocket = null;
    protected $server = null;
    protected $table = null;
    protected $u2fd = null;
    protected $fd2u = null;
    protected $event = null;

    /**
     * WebSocketEvent constructor.
     * @param Server $server server对象
     * @param Websocket $websocket websocket对线
     * @param Container $container 容器
     */
    public function __construct(Server $server, Websocket $websocket, Container $container, Event $event)
    {
        //这里取对象的方式有很多种,看个人习惯,在此列举其中几种方式
        $this->websocket = $websocket;//依赖注入的方式
        $this->server = $server;
        //think\table对象
        $this->table = $container->get(Table::class);//从容器中取
        //在think\Table的配置章节配置了两个table,使用方式如下
        //vendor\topthink\think-swoole\src\Table.php实现了__get魔术方法,因此直接table->表即可使用
        //或者table->get(表名)取得该表
        $this->u2fd = $this->table->u2fd;
        $this->fd2u = $this->table->fd2u;
        $this->event = $event;
    }

    //统一的消息格式
    protected function assemblyData(string $event, array $data, int $code = 0): string
    {
        return json_encode(['event' => $event, 'data' => $data, 'code' => $code], 320);
    }
}