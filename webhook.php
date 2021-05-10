<?php

use Rules\Rules;

include "./autoloader.php";
include "./func/global_func.php";
include "./config/config.php";
include "./classes/Error.php";

error_reporting(E_ALL & ~E_NOTICE);
ini_set('error_reporting', E_ALL);

set_error_handler("Classes\\Error::errorHandler");

Autoloader::register();

class Webhook
{ 
    public function __construct()
    {
    }

    public function start()
    {
        $input = file_get_contents('php://input');
        $json = json_decode($input);
        $text = mb_strtolower(trim($json->message->text));
        if (property_exists($json->message, 'http_answer') && $json->message->http_answer) {
            $GLOBALS['http_answer'] = true;
        } else {
            $GLOBALS['http_answer'] = false;
        }
        $rules = new Rules();
        $rules->create();
        $rules->process($text);
    }
}

$webhook = new Webhook();
$webhook->start();
