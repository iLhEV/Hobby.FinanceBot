<?php

namespace Classes;

class Error
{
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        p("error number: " . $errno);
        p("error str: " . $errstr);
        p("error file: " . $errfile);
        p("error line: " . $errline);
        p(debug_backtrace());
    }

}