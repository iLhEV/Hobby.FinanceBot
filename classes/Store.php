<?php

namespace Classes;

class Store
{
    public static $instances = [];

    public static function getInstance($name)
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        } else {
            return false;
        }
    }
    public static function setInstance($name, $object)
    {
        self::$instances[$name] = $object;
        return true;
    }

}