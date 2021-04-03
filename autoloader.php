<?php

class Autoloader {
    public static function register()
    {
        spl_autoload_register(function ($class) {
            if (strpos($class, "\\")) {
                $pieces = explode("\\", $class);
                $file = $_SERVER['DOCUMENT_ROOT'] . "/" . strtolower($pieces[0]) . "/" . $pieces[1] . ".php";
                require $file; 
                if ($pieces[0] == "Facades") {
                    $instance_class = $class::getFacadeAccessor();
                    $instance = new $instance_class();
                    $class::setFacadeApplication($instance);
                }
                return;
            }
        });
    }
}