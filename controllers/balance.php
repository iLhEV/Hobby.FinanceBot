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
            Tlgr::sendMessage($answer);
            return true;
        }
    }
}