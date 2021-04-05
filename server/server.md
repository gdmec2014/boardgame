服务端说明
================
接口地址：https://game.haibarai.com/
websocket地址：wss://game.haibarai.com/ws

一、websocket相关
1.数据传输
- PING关键字为2(请每秒ping一次，防止websocket断开)
```js
ws.send('2');
```
- PONG关键字为3
- MESSAGE关键字为4，发送消息是json字符串前面必须拼接“42”字符串
- 数据格式：['事件类型', '数据']
```js
ws.send('42' + JSON.stringify(['join', {room:room_id}]));
```