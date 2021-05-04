<?php

namespace Classes;

class DateCalc
{
    //Внутри класса использовуется строковое представление даты.
    //Сейчас это Y-m-d, т.е. с точностью до дня.
    private $date = "";

    public function __construct($date)
    {
        //// Проверку отключаю из соображений производительности
        ////Проверка на соответсвие формату Y-m-d
        $this->date = $date;
        if (!$this->checkFormat($date)) {
            return false;
        }
    }

    public function plus($type, $number)
    {
        //// Проверку отключаю из соображений производительности
        //// if (!is_int($number)) {
        ////     p("error 'number' should be integer");
        //// }
        //// $types = ['day', 'month', 'year'];
        //// if (!in_array($type, $types)) {
        ////     p("error 'type' should be one of " . implode(", ", $types));
        ////     return false;
        //// }
        return date('Y-m-d', strtotime($this->date .' +{$number} {$type}'));
    }

    private function checkFormat($date)
    {
        if (!preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $date)) {
            p("error input date format " . $date . "is invalid");
            return false;
        } else {
            return true;
        }
    }
}