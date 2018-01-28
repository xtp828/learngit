<?php
//进程异步队列
class SwooleProcessQueue{
	
	private $process;

	public function __construct()
	{
		$this->process = new swoole_process(array($this, 'run'), false, true);
		if(!$this->process->useQueue(222)){
			var_dump(swoole_strerror(swoole_errno()));exit;
		}
		$this->process->start();

		while (true) {
			echo "receive:".$this->process->pop().PHP_EOL;
		}
	}

	public function run($worker)
	{
		swoole_timer_tick(1000, function($timer_id){
			static $index = 0;
			$index = $index + 1;
			$this->process->push('hello');
			var_dump($index);
			if($index == 10){
				swoole_timer_clear($timer_id);
				$this->process->exit(0);
			}
		});
	}
}

new SwooleProcessQueue();
swoole_process::signal(SIGCHLD, function($signo) {
     while($rest = swoole_process::wait(false)){
     	echo 'PID = '.$rest['pid'];
     }
});