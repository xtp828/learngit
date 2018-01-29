<?php
class Server
{
    private $serv;
    private $http;
	private $map;
	private $serviceUrl;
 
 
    public function __construct() {


		$this->setServiceUrl();
		
		$table = new swoole_table(1024);
		$table->column('box', swoole_table::TYPE_STRING, 20);       //
		$table->column('fd', swoole_table::TYPE_INT, 6);       //
		$table->create();


        $this->serv = new swoole_server("0.0.0.0", 9501);
	
		$this->serv->table = $table;

        // $this->http = $this->serv->listen('0.0.0.0', 9502, SWOOLE_SOCK_TCP);
        
        $this->serv->set(array(
        	'daemonize' => 0,
        	'task_worker_num' => 4,
        	'max_conn' => 200,
        	'log_file' => '/opt/server/log/swoole.log',
        	'log_level' => 1,
        	'heartbeat_idle_time' => 600,
        	'heartbeat_check_interval' => 60
        	));

        $this->serv->on('receive', array($this, 'onReceive'));
        $this->serv->on('task', array($this, 'onTask'));

        $this->serv->on('connect', array($this, 'onConnect'));
        $this->serv->on('close', array($this, 'onClose'));
        $this->serv->on('finish', array($this, 'onFinish'));

        $this->serv->start();
    }

    //api地址
	public function setServiceUrl(){

		$this->serviceUrl['forward'] = 'http://swooleapi.byd.aoorange.cn/api/add?';//;
	}

   //接收数据
    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
		
		$str = "server receive fd:#".$fd."\t data:";
		$str .= $data;
		$this->logger($str);		
		
        $connection_info = $serv->connection_info($fd, $from_id);//获取连接信息
        switch ($connection_info['server_port']) {
            case 9501://终端向客户端发
            {
				$data = trim($data);
				//{"box":"D-1000","client":"13417307069","sign":"AEGRTEWTQ@%%TW","data":["FX456746131631641","FX456746131631641","FX456746131631641"]}
				$receive = $this->parseTerminal($data);//解析数据
				$terminalId = $receive['box'];//终端ID
				$exist = $this->serv->table->exist($terminalId);
				$insdata = array("box" => 5555, "fd"=>$fd);
				if($exist){
					$this->serv->table->del($terminalId);
					$this->serv->table->set($terminalId,$insdata);
				}else{
					$this->serv->table->set($terminalId,$insdata);
				}
				
				$taskid = $this->serv->task($data); //异步任务返回taskid
				$this->addLog($data);//写日志
				break;
            }
            case 9502://客户端向终端发
            {
				//因为http 过来有 头信息
				$start = strpos($data,'{');
				$end = strpos($data,'}');
				$command = substr($data,$start,($end-$start+1));

				$receive = $this->parseTerminal($command);
				$terminalId = $receive[1];//终端ID					
			
				$exist = $this->serv->table->exist($terminalId);
				if($exist){

					$redata = $this->serv->table->get($terminalId);

					$cResult = $this->serv->send($redata['fd'],$command );//发送命令给终端
				//	echo $cResult;
					
					$str = "server to client fd :".$redata['fd']." ,command:".$command.",cResult:".$cResult;

					$this->logger($str);

					
					if($cResult>0){
						$ress = "true,{$cResult}";
						$cResult = $this->serv->send($fd, $ress);//发送平台
					
					}else{
						$ress = "false,0";
						$cResult = $this->serv->send($fd, $ress);//发送平台						
					}
				

					$this->serv->close($fd); //关闭当前http						
				}else{
					$ress = "false,-1";
					$cResult = $this->serv->send($fd, $ress);//发送平台

					$this->serv->close($fd); //关闭当前http						
				}
                break;
            }
        }
    }


    public function parseTerminal($data){
				$data = trim($data);
				$receive =  explode("*",$data); 
			return $receive;
	}
    
	// public function onRequest(swoole_http_request $request, swoole_http_response $response){
	//    $response->end("eeee");
	// }


	public function onTask($serv, $task_id, $from_id, $data) {//异步调用
		
		 $fd = json_decode( $data , true )['fd'];
		 $data = json_decode( $data , true )['data'];
 
        if(strpos($data,'[') !== false &&  strpos($data,']')>0){
			
			$s = strpos($data,'[');//开始位置
			$len = strpos($data,']')+1;//长度
            $txt = array("appkey"=>"jfdja3","content"=>$data);

			$url = $this->serviceUrl['forward'].http_build_query($txt);

			$str = "get url Client:".$url."\t";

			$result = $this->getCurl($url);	

			$result = trim($result);
			
			$qData = json_decode($result,1);

			if($qData['return_code']==1){
				$toSendData = $qData['content'];
				$serv->send( $fd , $toSendData);
			}

        
			

			$str .= "Server to Client . fd:#".$fd." data:".$toSendData." \t Server reply data:".$result;
			$this->logger($str);

		} 

	}


	public function onConnect($serv, $fd) {
		
		$str = "Client: Connect. fd:#".$fd;
		$this->logger($str);
	// echo "Client: Connect.\n";

	}


	public function onClose($serv, $fd) {
		$str = "Client: Close. fd:#".$fd;
		$this->logger($str);
		//echo "Client: Close.\n";

	}


	public function onFinish($serv, $task_id, $data) {
		$str = "task: onFinish. task_id:#".$task_id;
		$this->logger($str);
		//echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;

	}


	public function logger($str){
		$fp = fopen("/opt/server/log/data.txt","a+")  ;
		fputs($fp,date('Y-m-d H:i:s').$str."\r\n\r\n");
		fclose($fp);	   
	}	

	public function getCurl($url,$data=NULL){

		$ch = curl_init();
		$header = array("Accept-Charset: utf-8");
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		
		if($data != NULL){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		return $temp;
	}
	
}

new Server();
