<?php
//创建Server对象，监听 127.0.0.1:9501端口
$serv = new swoole_server("127.0.0.1", 9501); 

$serv->set(array(
    'backlog' => 128,   //listen backlog
    'max_request' => 50,
    'daemonize' => false
));

//监听连接进入事件
$serv->on('connect', function ($serv, $fd) {  
	//echo $fd . PHP_EOL;
	//$serv->bind($fd,rand(1,100));
	// var_dump($serv->connection_info($fd));
    // echo "Client: Connect." . PHP_EOL;
    
});

//监听数据接收事件
$serv->on('receive', function ($serv, $fd, $from_id, $data) {

	print_r($data);
	$serv->send($fd, $fd."说：".$data);
	// foreach($serv->connections as $v)
	// {
	// 	echo $v;
	//     $serv->send($v, $fd."说：".$data);
	// }
    //$serv->send($fd, "Server: ".$fd.$data);
});

//监听连接关闭事件
$serv->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

//启动服务器
$serv->start(); 
