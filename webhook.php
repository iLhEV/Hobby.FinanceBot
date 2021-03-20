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
        $this->db = new PDO(
            "mysql:host=localhost;dbname=" . $GLOBALS['env']['db_base'] . ";charset=utf8",
            $GLOBALS['env']['db_user'],
            $GLOBALS['env']['db_pass'],
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ]
        );
    }

    public function start()
    {
        
        $input = file_get_contents('php://input');
        $json = json_decode($input);
        $text = trim($json->message->text);
        $params = [':text' => $text];
        $query = $this->db->prepare("INSERT INTO `tg_messages` SET `text`=:text");
        $query->execute($params);
        $found = [];
        foreach (['finance_operation'] as $func) {
            if ($this->$func($text)) {
                $found[] = $func;
            }
        }

        if (count($found) === 1) {
            return true;
        } elseif (count($found) > 1) {
            $this->sendMessage('Многозначно');
        } elseif (count($found) === 0) {
            $this->sendMessage('Не понял');
        }
    }

    protected function finance_operation($text)
    {
        if (preg_match('/(*UTF8)^([а-яёa-z]+)\s([\+\-0-9\.]+)$/ui', $text, $matches)) {
            $name = $matches[1];
            $val = $matches[2];
            $params = [':name' => $name, ':val' => $val];
            $query = $this->db->prepare("INSERT INTO `operations` SET `name`=:name, `val`=:val");
            $query->execute($params);            
            if ($query->rowCount()) {
                $this->sendMessage($matches);
                $this->sendMessage("Операция записана");
            } else {
                $this->sendMessage("Ошибка записи операции в БД");
            }
            return true;
        } else {
            return false;
        }
    }

    protected function sendMessage($text)
    {
        $this->client->sendMessage($text);
        //print_r($text);
    }
}

$webhook = new Webhook();
$webhook->start();

?>