<?php

include "./func/global_func.php";
include "./classes/tlgr.php";
include "./classes/file.php";
include "./facades/db.php";
include "./facades/tlgr.php";
include "./classes/database.php";
// include "./models/model.php";
// include "./models/account.php";
include "./controllers/balance.php";
include "./config/db.php";

error_reporting(E_ALL & ~E_NOTICE);
ini_set('error_reporting', E_ALL);

$database = new Database();
DB::setFacadeApplication($database);

$tlgr = new TlgrClient();
Tlgr::setFacadeApplication($tlgr);
$tlgr->sendMessage("Сообщение");

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
        $found = [];
        foreach([
            ['balance', 'get']
        ] as $route) {
            $class = ucfirst($route[0]);
            $action = $route[1];
            $controller = $class . 'Controller';
            $cntr = new $controller();
            if ($cntr->$action($text)) {
                return true;
            }
        }
        foreach ([
                // 'showBalanceValue',
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
            $query = DB::prepare("INSERT INTO `spendings` SET `name`=:name, `val`=:val");
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
            $query = DB::query("SELECT * FROM `spendings`" . $min_date_sql);
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

    protected function addBalanceValue($text)
    {
        if (preg_match('/(*UTF8)^баланс\s([а-яёa-z\s]+)\s([\+\-0-9\.]+)$/ui', $text, $matches)) {
            $account = $matches[1];
            $val = $matches[2];
            $params = [':name' => $account];
            $query = DB::prepare("SELECT * FROM `accounts` WHERE `name`=:name");
            $query->execute($params);
            
            if ($query->rowCount()) {
                $account_id = $query->fetch()['id'];
                $params = ['account_id' => $account_id, ':val' => $val];
                $query1 = DB::prepare("INSERT INTO `balance_values` SET `account_id`=:account_id, `val`=:val");
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
        Tlgr::sendMessage($text);
        //print_r($text);
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