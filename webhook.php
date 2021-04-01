<?php

include "./autoloader.php";
include "./func/global_func.php";
include "./config/db.php";
//include "./models/spending.php";

error_reporting(E_ALL & ~E_NOTICE);
ini_set('error_reporting', E_ALL);

Autoloader::register();

class Webhook
{ 
    protected $client = null;
    protected $file = null;

    public function __construct()
    {
        $this->file = new File();
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
            $controller = "" . $class . 'Controller';
            $cntr = new $controller();
            if ($cntr->$action($text)) {
                return true;
            }
        }

        $this->sendMessage('Не понял');
        $this->saveMessage($text, 1);
        return false;
    }

    protected function sendMessage($text)
    {
        Tlgr::sendMessage($text);
    }

    protected function saveMessage($text, $reason)
    {
        $params = [':text' => $text, ':reason' => $reason];
        $query = DB::prepare("INSERT INTO `tg_messages` SET `text`=:text, `reason`=:reason");
        $query->execute($params);
    }
}

$webhook = new Webhook();
$webhook->start();

?>