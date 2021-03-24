<?php

class Database
{
    private $conn;

    public function __construct()
    {
        $this->conn = $this->connect();
    }

    protected function connect()
    {
        $this->conn = new PDO(
            "mysql:host=localhost;dbname={$GLOBALS['env']['db_base']};charset=utf8",
            $GLOBALS['env']['db_user'],
            $GLOBALS['env']['db_pass'],
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ]
        );
    }    

    public function select()
    {
        print_r("select answer");
    }
}