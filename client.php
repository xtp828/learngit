<?php


function cPost($url,$post)
{
	//初始化
    $curl = curl_init();
    $hader = array("Accept-Charset:utf-");
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 1);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);
    //设置post数据
    $post_data = '[cs,4444,5|5555]';
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
    print_r($data);
}

// class Client
// {
//     private $client;

//     public function __construct()
//     {
//         $this->client = new swoole_client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
//     }

//     public function connect()
//     {
//     	$msg = $_GET['msg'];
//         if (!$fp = $this->client->connect('127.0.0.1', 9501, 1)) {
//             echo "Error : {$fp->errMsg}";exit;
//         }



//         $this->client->send($msg);
//         $message = $this->client->recv();
//         error_log($message."\r\n", 3, 'error.log');
//         echo "Get Message From Server:{$message}\n";
//     }
// }

// $client = new Client();

// $client->connect();

cPost('http://127.0.0.1:9501');