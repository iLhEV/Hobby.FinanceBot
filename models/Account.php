<?php

namespace Models;

use Facades\DB;

class Account
{
    //Get all accounts
    public function getAll()
    {
        return DB::query("SELECT * FROM `accounts` order by `id` ASC");
    }
    //Get account by name
    public function getByName($name)
    {
        $params = [':name' => $name];
        $query = DB::prepare("SELECT * FROM `accounts` WHERE `name`=:name");
        $query->execute($params);
        return $query;
    }
    //Add fixations to balance
    public function makeFixation($date)
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
}