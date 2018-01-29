<?php
class Server{
	private $serv;

	public function __construct() {
		$this->serv = new swoole_server("127.0.0.1", 9501);
		$this->serv->set(
			array(
				'worker_num' => 8,
			    'daemonize' => false,
		        'max_request' => 10000,
			)
		);
		$this->serv->on('Start', array($this, 'onStart'));
		$this->serv->on('WorkerStart', array($this, 'onWorkerStart'));
		$this->serv->on('Connect', array($this, 'onConnect'));
		$this->serv->on('Receive', array($this, 'onReceive'));
		$this->serv->on('Close', array($this, 'onClose'));
		$this->serv->start();
	}

	public function onStart($serv) {
		echo "Start\n";
	}

	public function onWorkerStart($serv, $worker_id) {
		if($worker_id == 0){
			swoole_timer_tick(1000,function($timer_id, $params){
				echo "Task is Running \n";
				echo "recv:{$params}\n";
			}, 'Hello');
		}
	}

	public function onConnect($serv, $fd, $from_id) {
		echo "Client {$fd} is connection and fromd_id is {$from_id}\n";
	}

	public function onClose($serv, $fd, $from_id) {
		echo "Client {$fd} is closed and fromd_id is {$from_id}\n";
	}

	public function onReceive($serv, $fd, $from_id, $data) {
		echo "Get Msg From Client {$fd}:{$data}\n";
		swoole_timer_after(1000,function() use($serv, $fd){
			echo "Timer after\n";
			$serv->send($fd, "hello later \n");
		});

		echo "Continue Handle Worker\n";
	}
}
new Server();