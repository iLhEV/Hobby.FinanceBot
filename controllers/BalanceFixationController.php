<?php

namespace Controllers;

use Facades\DB;
use Facades\Account;
use Facades\BalanceValues;
use Facades\Income;
use Facades\BalanceFixation;
use Facades\Spending;
use Facades\Tlgr;

class BalanceFixationController
{
    public function make()
    {
        if ($total = BalanceFixation::create()) {
            Tlgr::sendMessage('Баланс зафиксирован и равен ' . $total);
        } else {
            Tlgr::sendMessage('Ошибка фиксации баланса');
        }
        return true;
    }
    
    public function getAll()
    {
        if ($items = BalanceFixation::getAll()) {
            $answer = "";
            foreach ($items as $item) {
                $answer .= "#" . $item['id'] . " " . $item['total'] . " " . date("d.m H:m", strtotime($item['created_at'])) . PHP_EOL;
            }
            Tlgr::sendMessage($answer);
        } else {
            Tlgr::sendMessage("В базе нет фиксаций баланса");
        }
        return true;
    }
}