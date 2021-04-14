<?php

namespace Controllers;

use Facades\DB;
use Facades\Account;
use Facades\BalanceValues;
use Facades\Income;
use Facades\Spending;
use Facades\Tlgr;

class IncomeController
{
    public function add($input)
    {
        if (Income::add($input[0], $input[1])) {
            Tlgr::sendMessage("Доход записан");
        } else {
            Tlgr::sendMessage("Ошибка записи дохода в БД");
        }
        return true;
    }
    public function get()
    {
        if ($items = Income::getAll()) {
            $answer = ""; $sum = 0;
            foreach ($items as $item) {
                $answer .= "#" . $item['id'] . " " . date("d.m H:m", strtotime($item['created_at'])) . PHP_EOL;
                $answer .= $item['name'] . PHP_EOL;
                $answer .= $item['val'] . PHP_EOL;
                $sum += $item['val'];
                $answer .= PHP_EOL;
            }
            $answer .= "Общая сумма: " . $sum;
            Tlgr::sendMessage($answer);
        } else {
            Tlgr::sendMessage("В базе нет доходов");
        }
        return true;
    }
}