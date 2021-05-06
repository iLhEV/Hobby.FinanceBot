<?php

namespace Classes;

class MoneyFormat
{
    public static function format($val)
    {
        return number_format($val, 0, ".", ",") . " руб";
    }
}