<?php

namespace Facades;

class Fixation
{
    private static $app;


    private static function getFacadeRoot()
    {
        return self::$app;
    }

    public static function setFacadeApplication($app)
    {
        self::$app = $app;
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        return $instance->$method(...$args);
    }

    public static function getFacadeAccessor()
    {
        return 'Models\Fixation';
    }
}