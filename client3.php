<?php

class Client
{
    private $client;

    public function __construct()
    {
        $this->client = new swoole_client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
    }

    public function connect()
    {
    	$msg = $_GET['msg'];
        if (!$fp = $this->client->connect('127.0.0.1', 9501, 1)) {
            echo "Error : {$fp->errMsg}";exit;
        }

        

        $this->client->send($msg);
        $message = $this->client->recv();
        error_log($message."\r\n", 3, 'error.log');
        echo "Get Message From Server:{$message}\n";
    }
}

$client = new Client();

$client->connect();
