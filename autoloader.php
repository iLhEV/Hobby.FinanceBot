<?php

class Autoloader {
    public static function register()
    {
        spl_autoload_register(function ($class) {
            if (preg_match("/^(.*)Controller$/", $class, $mtchs)) {
                include $_SERVER['DOCUMENT_ROOT'] . "/controllers/" . strtolower($mtchs[1]) . '.php';
            } else {
                foreach (['facades', 'classes', 'models'] as $type) {
                    $file = $_SERVER['DOCUMENT_ROOT'] . "/" . $type . "/" . strtolower($class) . ".php";
                    if (file_exists($file)) {
                        require $file; 
                        if ($type == "facades") {
                            $instance_class = $class::getFacadeAccessor();
                            $instance = new $instance_class();
                            $class::setFacadeApplication($instance);
                        }
                        break;
                    }
                }
            }
        });
    }
}