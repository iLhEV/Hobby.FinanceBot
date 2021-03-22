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
                'showBalanceValue',
                'addBalanceValue',
                'writeSpendingPattern',
                'getSpendingPattern',
                
            ] as $pattern) {
            if ($this->$pattern($text)) {
                return true;
            }
        }

        $this->sendMessage('Не понял');
        $this->saveMessage($text, 1);
        return false;
    }

    protected function writeSpendingPattern($text)
    {
        if (preg_match('/(*UTF8)^([а-яёa-z\s\,0-9\-]+)\s([\+\-0-9\.]+)$/ui', $text, $matches)) {
            $name = $matches[1];
            $val = $matches[2];
            $params = [':name' => $name, ':val' => $val];
            $query = $this->db->prepare("INSERT INTO `spendings` SET `name`=:name, `val`=:val");
            $query->execute($params);            
            if ($query->rowCount()) {
                $this->sendMessage("Трата записана");
            } else {
                $this->sendMessage("Ошибка записи траты в БД");
            }
            return true;
        } else {
            return false;
        }
    }

    protected function getSpendingPattern($text) {
        $flag = false;
        $min_date_sql = "";
        $answer = "";
        $sum = 0;
        if ($text === "траты" || $text === "траты сегодня" || $text === "сегодня траты") {
            $min_date_sql = " WHERE created_at >= '" . date('Y-m-d') . "'";
            $flag = true;
        }
        if ($text === "все траты" || $text === "траты все") {
            $flag = true;
        }
        if ($flag) {
            $query = $this->db->query("SELECT * FROM `spendings`" . $min_date_sql);
            foreach($query as $item) {
                $answer .= "#" . $item['id'] . " " . date("d.m H:m", strtotime($item['created_at'])) . PHP_EOL;
                $answer .= $item['name'] . PHP_EOL;
                $answer .= $item['val'] . PHP_EOL;
                $sum += $item['val'];
                $answer .= PHP_EOL;
            }
            $answer .= "Общая сумма: " . $sum;
            $this->sendMessage($answer);
            return true;
        }
        return false;
    }

    protected function showBalanceValue($text)
    {
        if ($text === 'баланс') {
            $query = $this->db->query("SELECT * FROM `accounts` ORDER BY `id` ASC");
            $vals = [];
            foreach($query as $account) {
                $query1 = $this->db->query("SELECT * FROM `balance_values` WHERE `account_id` = {$account['id']} ORDER BY `id` DESC");
                if ($query1->rowCount()) {
                    $val = $query1->fetch();
                    $vals[$account['name']] = [$val['val'], date("d.m H:m", strtotime($val['created_at']))];
                } else {
                    $vals[$account['name']] = "-";
                }
            }
            $answer = "";
            $sum = 0;
            foreach ($vals as $account_name => $val) {
                $answer .= $account_name . ": ";
                if (is_array($val)) {
                    $answer .= $val[0] . " : " . $val[1];
                    $sum += $val[0];
                } else {
                    $answer .= $val;
                }
                $answer .= PHP_EOL;
            }
            $answer .= "Общий баланс: " . $sum;
            $this->sendMessage($answer);
            return true;
        }
    }

    protected function addBalanceValue($text)
    {
        if (preg_match('/(*UTF8)^баланс\s([а-яёa-z\s]+)\s([\+\-0-9\.]+)$/ui', $text, $matches)) {
            $account = $matches[1];
            $val = $matches[2];
            $params = [':name' => $account];
            $query = $this->db->prepare("SELECT * FROM `accounts` WHERE `name`=:name");
            $query->execute($params);
            
            if ($query->rowCount()) {
                $account_id = $query->fetch()['id'];
                $params = ['account_id' => $account_id, ':val' => $val];
                $query1 = $this->db->prepare("INSERT INTO `balance_values` SET `account_id`=:account_id, `val`=:val");
                $query1->execute($params);
                if($query1->rowCount()) {
                    $this->sendMessage('Значение записано');
                }
                return true;
            } else {
                $this->sendMessage('Нет такого счёта');
            }
            
        }
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