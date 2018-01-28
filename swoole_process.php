<?php
//进程间通讯
class SwooleProcess{
	private $process;

	public function __construct()
	{
		$this->process = new swoole_process(array($this, 'run'), false, true);
		$this->process->start();

		swoole_event_add($this->process->pipe, function($pipe){
			$data = $this->process->read();
			echo "receive:".$data.PHP_EOL;
		});
	}

	public function run($worker)
	{
		swoole_timer_tick(1000, function($timer_id){
			static $index = 0;
			$index = $index + 1;
			$this->process->write($index.'hello');
			var_dump($index);
			if($index == 10){
				swoole_timer_clear($timer_id);
				//$this->process->exit(0);
			}
		});
	}
}

new SwooleProcess();

//SIGCHLD子进程结束信号监听
swoole_process::signal(SIGCHLD, function($signo) {
	 //子进程结束后他会依次返回进程的PID等信息
     while($rest = swoole_process::wait(false)){
     	echo 'PID = '.$rest['pid'];
     }
});