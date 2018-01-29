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
        if (!$fp = $this->client->connect('127.0.0.1', 9501, 1)) {
            echo "Error : {$fp->errMsg}";exit;
        }

        // $msg_normal = "this is a msg";
        // $msg_eof = "this is  a msg\r\n";
        // $msg_length = pack('N', strlen($msg_normal)) . $msg_normal;

        fwrite(STDOUT, '输入消息：');
        $msg = trim(fgets(STDIN));
        $this->client->send($msg);
        $message = $this->client->recv();
        echo "Get Message From Server:{$message}\n";
    }
}


$client = new Client();

$client->connect();