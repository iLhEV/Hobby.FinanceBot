<?php

namespace Classes;

use \DateTime;

class DateCalc
{
    public static function addZero($num)
    {
        $str = strval($num);
        if (strlen($str) === 1) $str = "0" . $str;
        return $str;
    }

    //Формат должен быть Y-m-d или d-m-Y
    public static function reverse($date, $newSeparator = '')
    {
        //Найти сепаратор
        if ($separator = self::findSeparator($date)) {
            $exploded = explode($separator, $date);
            if ($newSeparator) $separator = $newSeparator;
            return $exploded[2] . $separator . $exploded[1] . $separator . $exploded[0];
        } else {
            //Ошибка если сепаратор не определён автоматически 
            return false;
        }
    }

    //Определяет сепаратор
    public static function findSeparator($date)
    {
        //Найти сепаратор
        if (preg_match('/([\-\.\_])/', $date, $matches)) {
            $separator = $matches[0];
            return $separator;
        } else {
            return false;
        }
    }

    //Извлекает номер месяца из даты
    public static function fetchMonthNumber($date)
    {
        if ($separator = self::findSeparator($date)) {
            $exploded = explode($separator, $date);
            $monthNum = $exploded[1];
            return intval($monthNum);
        } else {
            return false;
        }
    }

    //Добывает имя месяца из даты
    public static function fetchMonthName($date)
    {
        if ($separator = self::findSeparator($date)) {
            $exploded = explode($separator, $date);
            $monthNum = $exploded[1];
            return self::getMonthName($monthNum);
        } else {
            return false;
        }
    }

    //Извлекает год из даты
    public static function fetchYear($date) {
        if ($separator = self::findSeparator($date)) {
            $exploded = explode($separator, $date);
            $year = $exploded[0];
            return $year;
        } else {
            return false;
        }
    }

    //Добывает число и имя месяца строковое из даты
    public static function fetchDayAndMonthName($date)
    {
        if ($separator = self::findSeparator($date)) {
            $exploded = explode($separator, $date);
            $dayStr = $exploded[0];
            $monthName = self::fetchMonthName($date);
            return $dayStr . " " . $monthName;
        } else {
            return false;
        }
    }

    //Извлекает число из даты в формате d.m.Y
    public static function fetchDay($date)
    {
        if ($separator = self::findSeparator($date)) {
            $exploded = explode($separator, $date);
            $dayStr = $exploded[0];
            return intval($dayStr);
        } else {
            return false;
        }
    }


    //Возвращает русское название месяца по его номеру
    public static function getMonthName($num_str)
    {
        $num = intval($num_str);
        $months = [
            1       =>    'январь',
            2       =>    'февраль',
            3       =>    'март',            
            4       =>    'апрель',
            5       =>    'май',            
            6       =>    'июнь',
            7       =>    'июль',            
            8       =>    'август',
            9       =>    'сентябрь',            
            10      =>    'октябрь',
            11      =>    'ноябрь',            
            12      =>    'декабрь'
        ];
        if (isset($months[$num])) {
            return $months[$num];
        } else {
            return false;
        }
    }

    public static function getMonth($date)
    {
        return intval(date("m", strtotime($date)));
    }

    //Получить номер предыдущего месяца
    public static function getPreviousMonth($month_num)
    {
        if ($month_num === 1) {
            return 12;
        } else {
            return $month_num-1;
        }
    }
    //Получить первый день предыдущего месяца
    public static function getFirstDayOfPreviousMonth($input)
    {
        $res = self::getPreviousMonth($input);
        return [1, $res[0], $res[1]];
    }

    //Получить номер недели в рамках года по дате
    public static function getWeekNumberOfYear($day)
    {
        $date = new DateTime($day);
        return intval($date->format("W"));
    }

    public static function getToday()
    {
        return date("Y-m-d");
    }
}