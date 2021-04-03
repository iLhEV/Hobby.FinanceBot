<?php

include "./autoloader.php";
include "./func/global_func.php";
include "./config/db.php";
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
    }

    public function start()
    {
        $input = file_get_contents('php://input');
        $json = json_decode($input);
        $text = mb_strtolower(trim($json->message->text));
        $json->message->http_answer ? $GLOBALS['http_answer'] = true : $GLOBALS['http_answer'] = false;
        $found = [];
        
        foreach([
            ['balance', 'get'],
            ['balance', 'addValue'],
            ['spending', 'getByCategories'],
            ['spending', 'add'],
            ['spending', 'get'],
        ] as $route) {
            $class = ucfirst($route[0]);
            $action = $route[1];
            $controller = "Controllers\\" . $class . 'Controller';
            $cntr = new $controller();
            
            if ($cntr->$action($text)) {
                exit;
                return true;
            }
        }
        
        Facades\Tlgr::sendMessage('Не понял');
        $this->saveMessage($text, 1);
        return false;
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