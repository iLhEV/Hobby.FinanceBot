<?php

namespace Models;

use Facades\DB;

class Income
{
    //Get all accounts
    public function getTotal()
    {
        $query = DB::query("SELECT sum(val) as summa FROM `incomes`");
        if (!$income_sum = $query->fetchColumn()) $income_sum = 0;
        return $income_sum;
    }
}