<?php

namespace Models;

use Facades\DB;
use Facades\BalanceValues;

class BalanceFixation
{
    //Create fixation
    public function create()
    {
        $total = BalanceValues::total();
        $query = DB::query("INSERT INTO `balance_fixations` SET `total`={$total}");
        if ($query->rowCount()) {
            return $total;
        } else {
            return false;
        }
    }
    public function getAll()
    {
        $query = DB::query("SELECT * FROM `balance_fixations` ORDER BY `id` ASC");
        if ($query->rowCount()) {
            return $query;
        } else {
            return false;
        }
    }
}