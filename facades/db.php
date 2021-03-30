<?php

class DB extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Database';
    }
}