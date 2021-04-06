<?php

namespace Models;

use Facades\DB;

class Fixation
{
    //Create fixation
    public function create($date)
    {
        $params = [':date' => $date];
        $query = DB::prepare("INSERT INTO `fixations` SET `date`=:date");
        $query->execute($params);
        if ($query->rowCount()) {
            return DB::lastInsertId();
        } else {
            return false;
        }
    }
    public function getAll()
    {
        $query = DB::query("SELECT * FROM `fixations` ORDER BY `id` ASC");
        if ($query->rowCount()) {
            return $query;
        } else {
            return false;
        }
    }
}