<?php

include "./classes/client.php";
include "./classes/file.php";
include "./classes/db.php";
include "./config/db.php";

error_reporting(E_ALL & ~E_NOTICE);
ini_set('error_reporting', E_ALL);


class Webhook
{ 
    protected $client = null;
    protected $file = null;
    protected $db = null;

    public function __construct()
    {
        $this->client = new Client();
        $this->file = new File();
        $this->db = new PDO("mysql:host=localhost;dbname=" . $GLOBALS['env']['db_base'] . ";charset=utf8mb4", $GLOBALS['env']['db_user'], $GLOBALS['env']['db_pass']);
    }

    public function start()
    {
        $input = file_get_contents('php://input');
        $json = json_decode($input);
        $params = [':text' => $json->message->text];
        $query = $this->db->prepare("INSERT INTO `tg_messages` SET `text`=:text");
        $query->execute($params);
        $num = $query->rowCount();
        $text = "Вставлено $num записей с текстом '" . $json->message->text . "' в БД";
        $this->client->sendMessage($text);
    }
}

$webhook = new Webhook();
$webhook->start();

?>