<?php

namespace Models;

use Facades\DB;

class BalanceValues
{
    //Get balance values by account id
    public function getByAccountId($account_id)
    {
        return DB::query("SELECT * FROM `balance_values` WHERE `account_id` = {$account_id} ORDER BY `id` DESC");
    }
    //Add account
    public function add($account_id, $val)
    {
        $params = ['account_id' => $account_id, ':val' => $val];
        $query = DB::prepare("INSERT INTO `balance_values` SET `account_id`=:account_id, `val`=:val");
        $query->execute($params);
        return $query;
    }
}