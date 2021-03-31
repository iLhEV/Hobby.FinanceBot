<?php

class BalanceController
{
    public function get($text)
    {
        if ($text == 'баланс') {
            $query = DB::query("SELECT * FROM `accounts` ORDER BY `id` ASC");
            $vals = [];
            foreach($query as $account) {
                $query1 = DB::query("SELECT * FROM `balance_values` WHERE `account_id` = {$account['id']} ORDER BY `id` DESC");
                if ($query1->rowCount()) {
                    $val = $query1->fetch();
                    $vals[$account['name']] = [$val['val'], date("d.m H:m", strtotime($val['created_at']))];
                } else {
                    $vals[$account['name']] = "-";
                }
            }
            $sum = 0; $answer = "";
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
            $answer .= "Фактический баланс: " . $sum . PHP_EOL;
            $answer .= "Расчётный баланс: " . $this->countedBalance()[2];
            Tlgr::sendMessage($answer);
            return true;
        }
    }

    public function addValue($text)
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
                    Tlgr::sendMessage('Значение записано');
                }
                return true;
            } else {
                Tlgr::sendMessage('Нет такого счёта');
            }
            
        }
    }

    private function countedBalance()
    {
         
        $st = DB::query("SELECT sum(val) as summa FROM `incomes`");
        if (!$income_sum = $st->fetchColumn()) $income_sum = 0;
        $st = DB::query("SELECT sum(val) as summa FROM `spendings`");
        if (!$spending_sum = $st->fetchColumn()) $spending_sum = 0;
        return [$income_sum, $spending_sum, $income_sum - $spending_sum];
    }
}