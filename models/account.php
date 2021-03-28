<?php

class Account extends Model
{
    public $name;
    private $table = 'accounts';

    public function first()
    {
        $sth = DB::query("SELECT * from `{$this->table}` LIMIT 1");
        $item = $sth->fetch();
        $this->name = $item['name'];
        return $this;
    }

    public function all()
    {
        
    }
}