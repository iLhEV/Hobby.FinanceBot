<?php

namespace Controllers;

use Facades\DB;
use Facades\Account;
use Facades\BalanceValues;
use Facades\Income;
use Facades\Spending;
use Facades\Tlgr;

class BalanceController
{
    public function get($text)
    {
        if ($text == 'баланс' || $text == 'бал') {
            $query = Account::getAll();
            $vals = [];
            foreach($query as $account) {
                $query1 = BalanceValues::getByAccountId($account['id']);
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
            $query = Account::getByName($account);
            if ($query->rowCount()) {
                $account_id = $query->fetch()['id'];
                $query1 = BalanceValues::add($account_id, $val);
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
        $income_sum = Income::getTotal();
        $spending_sum = Spending::getTotal();
        return [$income_sum, $spending_sum, $income_sum - $spending_sum];
    }
}