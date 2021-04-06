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
    public function add($text)
    {
        if (preg_match('/(*UTF8)^доход\s([а-яёa-z\s]+)\s([\+\-0-9\.]+)$/ui', $text, $matches)) {
            if (Income::add($matches[1], $matches[2])) {
                Tlgr::sendMessage("Доход записан");
            } else {
                Tlgr::sendMessage("Ошибка записи дохода в БД");
            }
            return true;
        } else {
            return false;
        }
    }
    public function get($text)
    {
        if ($text == "доход" || $text == "дох" || $text == "дх") {
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
        return false;
    }
}