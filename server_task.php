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
		        'task_worker_num' => 8,
			)
		);
		$this->serv->on('Start', array($this, 'onStart'));
		$this->serv->on('Connect', array($this, 'onConnect'));
		$this->serv->on('Receive', array($this, 'onReceive'));
		$this->serv->on('Close', array($this, 'onClose'));
		//task test
		$this->serv->on('Task', array($this, 'onTask'));
		$this->serv->on('Finish', array($this, 'onFinish'));
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
		$data = json_encode(array(
			'task' => 'task_1',
			'params' => $data,
			'fd' => $fd,
		));
		$serv->task($data);
	}

	public function onTask($serv, $task_id, $from_id, $data) {
		echo "This is task {$task_id} from Worker {$from_id}\n";
		echo "Data is {$data}\n";

		$data =  json_decode($data, true);
		var_dump($data);

		$serv->send($data['fd'], 'Hello Task');
		return 'Finish';
	}

	public function onFinish($serv, $task_id, $data){
		echo "Task Result is {$data}\n";
	}
}
new Server();