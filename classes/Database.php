<?php

namespace Classes;

use \PDO;

class Database
{
    private $pdo;
    private $st;

    public function __construct()
    {
        return $this->pdo = $this->conn();
    }

    private function conn()
    {
        return new PDO(
            "mysql:host=localhost;dbname={$GLOBALS['env']['db_base']};charset=utf8",
            $GLOBALS['env']['db_user'],
            $GLOBALS['env']['db_pass'],
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ]
        );
    }    

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }

    public function execute()
    {
        return $this->pdo->execute(); 
    }

    public function query($sql)
    {
        return $this->pdo->query($sql);
    }
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}