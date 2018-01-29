<?php
class Server{
	private $serv;

	public function __construct() {
		$this->serv = new swoole_server("0.0.0.0", 9501);
		$this->serv->set(
			array(
				'worker_num' => 1,
			)
		);
		$this->serv->on('Start', array($this, 'onStart'));
		$this->serv->on('Connect', array($this, 'onConnect'));
		$this->serv->on('Receive', array($this, 'onReceive'));
		$this->serv->on('Close', array($this, 'onClose'));
		$this->serv->start();
	}

	public function onStart($serv) {
		echo "Start\n";
	}

	public function onConnect($serv, $fd, $from_id) {
		echo "Client {$fd} is connection and fromd_id is {$from_id}\n";
	}

	public function onClose($serv, $fd, $from_id) {
		echo "Client {$fd} is closed and fromd_id is {$from_id}\n";
	}

	public function onReceive($serv, $fd, $from_id, $data) {
		echo "Get Msg From Client {$fd}:{$data}\n";
		foreach ($serv->connections as $value) {
			if($fd != $value){
				$serv->send($value, $data);
			}
		}
	}
}
new Server();