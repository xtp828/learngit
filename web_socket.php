<?php

$rds = new Redis();
$rds->connect('127.0.0.1', 6379, 1);
$rds->flushAll();

$server = new swoole_websocket_server("0.0.0.0", 9502);
$server->user = array();

$server->set(
    array(
        'worker_num' => 2,    //开启两个worker进程
        'daemonize' => 0,
        'heartbeat_idle_time' => 600,
        'heartbeat_check_interval' => 5,
        'task_worker_num' => 4,

        //'pid_file' => __DIR__.'/server.pid',
        //'log_level' => 0,
        // 'log_file' => __DIR__.'/swoole.log',
        // 'dispatch_func' => function ($serv, $fd, $type, $data) {
        //     return 0;
        // },
    )
);
$server->on('open', function (swoole_websocket_server $server, $request) {
    //$server->push($request->fd,'我注册成功了-'.$request->fd);
    //$server->protect(1, 1);//受保护fd,可以不受心跳控制
    //$server->user[$request->fd] = $request->fd;
    global $rds;
    //$rds->set($request->fd, 1);
    $rds->hset('key', $request->fd,1);
});

// $server->on('WorkerStart',function($serv, $worker_id){  
//     echo '55556666';
// });  

$server->on('message', function (swoole_websocket_server $server, $frame) {
    //echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    //$server->push($frame->fd, "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n");
    $msg = json_decode($frame->data,true);
    if(is_array($msg)){
        $msg['fd'] = $frame->fd;
    }
    echo 'worker_id:'.$server->worker_id . "\n";
    // var_dump($server->user);
    // var_dump($server->connections);
    //print_r($msg);
    $task_id = $server->task($msg);
    echo "Dispath AsyncTask: id=$task_id\n".PHP_EOL;
});

$server->on('close', function ($ser, $fd) {
    //$ser->push($fd,'我关闭了');
    unset($ser->user[$fd]);
    echo "client {$fd} closed\n";
});

//处理异步任务
$server->on('task', function ($server, $task_id, $from_id, $data) {
    global $rds;
    $user = $rds->hgetall('key');
    echo 'Task_id'. $server->worker_id . "\n";
    var_dump($user);
    if(is_array($user)){
        foreach ($user as $key => $value) {
            if($server->connection_info($key)){
                $server->push($key, $data['fd'].'对全部人说：hello');    
            }
        }
    }
    /*if(is_array($server->user)){
        foreach ($server->user as $value) {
            if($msg['login']){
                $server->push($value, $data['fd'].'对全部人说，我来了');
            }else{
                
                $server->push($value, $data['fd'].'对全部人说：'.$data);
            }
        }
    }*/

    return 'success';
    //$serv->finish("$data -> OK");
});

//处理异步任务的结果
$server->on('finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
});


$server->start();