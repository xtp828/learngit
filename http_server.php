<?php

$serv = new swoole_http_server("0.0.0.0", 9501);

$serv->set(array(
	'package_max_length' => 20000000,
	));

$serv->on('request', function($request, $response) use($serv){
	var_dump($request);
	
	if($request->server['request_method'] == 'get'){
		return;
	}

	$file 			= $request->files['up_img'];
	$file_name 		= $file['name'];
	$tmp_path 		= $file['tmp_name'];

	$upload_path 	= __DIR__ . '/Uploader/';
	if(!file_exists($upload_path)){
		mkdir($upload_path);
	} 

	$result = move_uploaded_file($tmp_path, $upload_path.$file_name);
	$result = $result ? 'success' : 'fail';
	$response->end($result);
});

$serv->start();