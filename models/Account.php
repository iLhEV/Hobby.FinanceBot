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
}