<?php

namespace Controllers;

use Facades\DB;
use Facades\Account;
use Facades\BalanceValues;
use Facades\Income;
use Facades\Fixation;
use Facades\Spending;
use Facades\Tlgr;

class FixationController
{
    public function make($text)
    {
        if (preg_match('/(*UTF8)^фикс\s([0-9]{2})\.([0-9]{2})$/ui', $text, $matches)) {
            $date = date("Y") . "-" . $matches[2] . "-" . $matches[1];
            if (Fixation::create($date)) {
                Tlgr::sendMessage('Баланс зафиксирован на ' . $date);
            } else {
                Tlgr::sendMessage('Ошибка фиксации');
            }
            return true;
        }
        return false;
    }
    
    public function getAll($text)
    {
        if ($text == "фикс" || $text == "фиксации") {
            if ($items = Fixation::getAll()) {
                $answer = "";
                foreach ($items as $item) {
                    $answer .= "#" . $item['id'] . " " . date("d.m H:m", strtotime($item['date'])) . PHP_EOL;
                    $answer .= PHP_EOL;
                }
                Tlgr::sendMessage($answer);
            } else {
                Tlgr::sendMessage("В базе нет фиксаций");
            }
            return true;
        }
        return false;
    }
}