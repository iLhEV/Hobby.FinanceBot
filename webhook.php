<?php
echo "data";
$file = "test.txt";
$input = file_get_contents('php://input');
$json = json_decode($input);
file_put_contents($file, $json->message->text);
$text_params = http_build_query(['text' => $json->message->text]);
$url = "https://api.telegram.org/bot1722248171:AAGJPqhLEsHn_oYx9ldGbaYR68vu7NmVrG8/sendMessage?chat_id=1349171752&" . $text_params; 
file_get_contents($url);
?>