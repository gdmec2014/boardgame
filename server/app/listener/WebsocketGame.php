<?php
declare (strict_types=1);

namespace app\listener;

use app\Request;

class WebsocketGame extends BaseEvent
{
    public function onConnect(Request $request)
    {
        //onOpen触发的事件,传入的是一个request对象

        //模拟产生一个用户编号
        $uid = rand(100, 999);
        //将uid与fd用table进行关联,set方法的key必须为string类型
        //通过websocket的上下文取得fd:$this->websocket->getSender()
        $this->table->u2fd->set((string)$uid, ['fd' => $this->websocket->getSender()]);
        //将fd与uid用table进行关联
        //注意传入数组的key必须与tables中的columns的name值相同,否则无法写入
        $this->table->fd2u->set((string)$this->websocket->getSender(), ['uid' => $uid]);
        //这里是对所有的连接发送一次广播,消息为online,当前连接用户的uid,在线数量,当前连接用户的fd值
        foreach ($this->table->u2fd as $row) {
            $this->server->push($row['fd'], json_encode(['msg' => 'online', 'uid' => $uid, 'online_count' => $this->u2fd->count(), 'fd' => $this->websocket->getSender()], 320));
        }
    }

    public function onClose()
    {
        //onClose触发的事件

        //将离线的用户从关系表中移除
        $uid = $this->table->fd2u->get((string)$this->websocket->getSender(), 'uid');
        //注意传入参数必须为string类型
        $this->table->u2fd->del((string)$uid);
        $this->table->fd2u->del((string)$this->websocket->getSender());
        //广播下线
        foreach ($this->table->u2fd as $row) {
            $this->server->push($row['fd'], json_encode(['msg' => 'offline', 'uid' => $uid, 'online_count' => $this->u2fd->count(), 'fd' => $this->websocket->getSender()], 320));
        }
    }

    public function onEvent($event)
    {
        if(is_string($event['type'])) {
            $this->event->trigger('swoole.websocket.Game.' . $event['type'], $event['data']);
        }
    }

    public function onPoint($event)
    {
        //点对点消息 即json中event值为point会触发该方法
        //json:{"event":"point","data":{"to":uid,"content":"wsmsg"}}

        //用uid从列表中查找该用户是否在线
        $toFd = $this->table->u2fd->get((string)$event['to'], 'fd');
        if ($toFd === false) {
            if (isset($event['api'])) {
                return 'offline';
            }
            $this->server->push($this->websocket->getSender(), $event['to'] . ' is not online');
        }
        //判断是否来自api消息
        if (isset($event['api'])) {
            //消息下放
            $this->server->push($toFd, $this->assemblyData('point', ['sender' => $event['uid'] ?? 0, 'content' => $event['content']]));
        } else {
            //消息下放
            $this->server->push($toFd, $this->assemblyData('point', ['sender' => $this->table->fd2u->get((string)$this->websocket->getSender(), 'uid'), 'content' => $event['content']]));
        }

    }

    public function onPing()
    {
        //响应ping消息
        //{"event":"ping","data":""}
        $this->server->push($this->websocket->getSender(), $this->assemblyData('pong', []));
    }

    //more event function
}