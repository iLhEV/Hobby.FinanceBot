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
        $found = [];
        foreach ([
                'financeOperationPattern',
                'getOperationsPattern'
            ] as $pattern) {
            if ($this->$pattern($text)) {
                $found[] = $pattern;
            }
        }
        
        if (count($found) === 1) {
            return true;
        } elseif (count($found) > 1) {
            $this->sendMessage('Многозначно');
            $this->saveMessage($text, 2);
        } elseif (count($found) === 0) {
            $this->sendMessage('Не понял');
            $this->saveMessage($text, 1);
        }
    }

    protected function financeOperationPattern($text)
    {
        if (preg_match('/(*UTF8)^([а-яёa-z\s]+)\s([\+\-0-9\.]+)$/ui', $text, $matches)) {
            $name = $matches[1];
            $val = $matches[2];
            $params = [':name' => $name, ':val' => $val];
            $query = $this->db->prepare("INSERT INTO `operations` SET `name`=:name, `val`=:val");
            $query->execute($params);            
            if ($query->rowCount()) {
                //$this->sendMessage($matches);
                print_r("success");
                $this->sendMessage("Операция записана");
            } else {
                $this->sendMessage("Ошибка записи операции в БД");
            }
            return true;
        } else {
            return false;
        }
    }

    protected function getOperationsPattern($text) {
        $flag = false;
        $min_date_sql = "";
        $answer = "";
        if ($text === "операции" || $text === "операции сегодня" || $text === "сегодня операции") {
            $min_date_sql = " WHERE created_at >= '" . date('Y-m-d') . "'";
            $flag = true;
        }
        if ($text === "все операции" || $text === "операции все") {
            $flag = true;
        }
        if ($flag) {
            $query = $this->db->query("SELECT * FROM `operations`" . $min_date_sql);
            foreach($query as $item) {
                $answer .= "#" . $item['id'] . " " . date("d.m H:m", strtotime($item['created_at'])) . PHP_EOL;
                $answer .= $item['name'] . PHP_EOL;
                $answer .= $item['val'] . PHP_EOL;
                $answer .= PHP_EOL;
            }
            $this->sendMessage($answer);
            return true;
        }
        return false;
    }

    protected function sendMessage($text)
    {
        $this->client->sendMessage($text);
        //print_r($text);
    }

    protected function saveMessage($text, $reason)
    {
        $params = [':text' => $text, ':reason' => $reason];
        $query = $this->db->prepare("INSERT INTO `tg_messages` SET `text`=:text, `reason`=:reason");
        $query->execute($params);
    }
}

$webhook = new Webhook();
$webhook->start();

?>