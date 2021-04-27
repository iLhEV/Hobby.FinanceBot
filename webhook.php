<?php

use Classes\Store;
use Classes\Bot;
use Classes\RegExp;
use Rules\Rules;

include "./autoloader.php";
include "./func/global_func.php";
include "./config/config.php";
include "./classes/Error.php";
//include "./models/spending.php";

error_reporting(E_ALL & ~E_NOTICE);
ini_set('error_reporting', E_ALL);

set_error_handler("Classes\\Error::errorHandler");

Autoloader::register();

class Webhook
{ 
    public function __construct()
    {
        //Store::setInstance('bot', new Bot());
    }

    public function start()
    {
        // p(intval(RegExp::resolve('б|бал|баланс {string} {amount}', 'баланс альфа 1.0')));
        // exit;
        $input = file_get_contents('php://input');
        $json = json_decode($input);
        $text = mb_strtolower(trim($json->message->text));
        if (property_exists($json->message, 'http_answer') && $json->message->http_answer) {
            $GLOBALS['http_answer'] = true;
        } else {
            $GLOBALS['http_answer'] = false;
        }
        $found = [];
        $rules = new Rules();
        $rules->create();
        $rules->process($text);
        
        // foreach([
        //     ['BalanceFixation', 'make'],
        //     ['BalanceFixation', 'getAll'],
        //     ['income', 'add'],
        //     ['income', 'get'],
        //     ['balance', 'get'],
        //     ['balance', 'addValue'],
        //     ['spending', 'getByCategories'],
        //     ['spending', 'add'],
        //     ['spending', 'get'],
        // ] as $route) {
        //     $class = ucfirst($route[0]);
        //     $action = $route[1];
        //     $controller = "Controllers\\" . $class . 'Controller';
        //     $cntr = new $controller();
            
        //     if ($cntr->$action($text)) {
        //         exit;
        //         return true;
        //     }
        // }
        
        // Facades\Tlgr::sendMessage('Не понял');
        // $this->saveMessage($text, 1);
        // return false;
    }

    protected function saveMessage($text, $reason)
    {
        $params = [':text' => $text, ':reason' => $reason];
        $query = Facades\DB::prepare("INSERT INTO `tg_messages` SET `text`=:text, `reason`=:reason");
        $query->execute($params);
    }
}

$webhook = new Webhook();
$webhook->start();

?>