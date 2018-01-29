<?php
/*
$redisClass = new Redis();
$redisClass->connect('127.0.0.1', 6379, 1);
$result = $redisClass->hmset('mytest:phpredis1', ['aa' => 'aa', 'bb' => 'bb']); // 原生的支持数组
var_dump($result);*/
//echo __LINE__;exit;

try{
	$redis = new Swoole\Redis;
	$redis->connect('127.0.0.1', 6379, function ($redis, $result) {
		$data = '123456';
	    $redis->set('test_key', $data, function ($redis, $result) {
	        $redis->get('test_key', function ($redis, $result) {
	            var_dump($result);
	        });
	    });
	});
}catch(Exception $e){
	var_dump($e);
}

