<?php

namespace Classes;

class MoneyFormat
{
    //Форматирует в рубль и при необходимости "подгоняет" строку под заданную длину
    public static function format($val, $currency = "")
    {
        return number_format($val, 0, ".", ",") . " " . $currency;
    }
}