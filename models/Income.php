<?php

namespace Models;

use Facades\DB;

class Income
{
    //Get summa of all incomes
    public function getTotal()
    {
        $query = DB::query("SELECT sum(val) as summa FROM `incomes`");
        if (!$income_sum = $query->fetchColumn()) $income_sum = 0;
        return $income_sum;
    }
    public function add($name, $val)
    {
        $params = [':name' => $name, ':val' => $val];
        $query = DB::prepare("INSERT INTO `incomes` SET `name`=:name, `val`=:val");
        $query->execute($params);
        if ($query->rowCount()) {
            return DB::lastInsertId();
        } else {
            return false;
        }
    }
    public function getAll()
    {
        $query = DB::query("SELECT * FROM `incomes` ORDER BY `id` ASC");
        if ($query->rowCount()) {
            return $query;
        } else {
            return false;
        }
    }
}