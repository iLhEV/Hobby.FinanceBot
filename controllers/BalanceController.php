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
    public function get($rule)
    {
        $accounts = Account::getAll();
        $balanceCollection = [];
        //Iterate over accounts
        foreach($accounts as $account) {
            $collectionValueModel = BalanceValues::getByAccountId($account['id']);
            if ($collectionValueModel->rowCount()) {
                $collectionValue = $collectionValueModel->fetch();
                $balanceCollection[$account['name']] = [
                    'value' => $collectionValue['val'],
                    'time' => date("Y-m-d H:i:s", strtotime($collectionValue['created_at'])),
                ];
            } else {
                $balanceCollection[$account['name']] = "-";
            }
        }
        $sum = 0; $answer = "";
        foreach ($balanceCollection as $accountName => $collectionValue) {
            //Фильтрую значения по датам
            if ($period = $rule->dateFilter->getPeriod()) {
                if ($period[0]) {
                    if ($collectionValue['time'] < $period[0]) continue;
                }
                if ($period[1]) {
                    if ($collectionValue['time'] > $period[1]) continue;
                }
            }
            //Формирую строку
            $answer .= $accountName . ": ";
            if (is_array($collectionValue)) {
                $answer .= $collectionValue['value'] . " : " . $collectionValue['time'];
                $sum += $collectionValue['value'];
            } else {
                $answer .= $collectionValue;
            }
            $answer .= PHP_EOL;
        }
        $answer .= "Фактический баланс: " . $sum . PHP_EOL;
        $answer .= "Расчётный баланс: " . $this->countedBalance()[2];
        Tlgr::sendMessage($answer);
        return true;
    }

    public function setVal($rule)
    {
        $account = $rule->foundMatches[0];
        $val = $rule->foundMatches[1];
        $query = Account::getByName($account);
        if ($query->rowCount()) {
            $account_id = $query->fetch()['id'];
            $query1 = BalanceValues::add($account_id, $val);
            if($query1->rowCount()) {
                Tlgr::sendMessage('Значение баланса записано');
            }
            return true;
        } else {
            Tlgr::sendMessage('Нет такого счёта');
        }
    }

    private function countedBalance()
    {
        $income_sum = Income::getTotal();
        $spending_sum = Spending::getTotal();
        return [$income_sum, $spending_sum, $income_sum - $spending_sum];
    }
}