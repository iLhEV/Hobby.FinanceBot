<?php

namespace Classes;

class DateTransform
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

    //Добывает имя месяца из даты
    public static function fetchMonthName($date)
    {
        if ($separator = self::findSeparator($date)) {
            $exploded = explode($separator, $date);
            $monthNum = $exploded[1];
            return self::getMonthNameByNum($monthNum);
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
    //Извлекает номер дня
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
    public static function getMonthNameByNum($numStr)
    {
        $num = intval($numStr);
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
    //Получить номер предыдущего месяца и год этого месяца
    public static function getPreviousMonth($input)
    {
        $currMonthNum = intval($input[0]);
        $currYearNum = intval($input[1]);
        if ($currMonthNum === 1) {
            return [12, $currYearNum - 1];
        } else {
            return [$currMonthNum-1, $currYearNum];
        }
    }
    //Получить первый день предыдущего месяца
    public static function getFirstDayOfPreviousMonth($input)
    {
        $res = self::getPreviousMonth($input);
        return [1, $res[0], $res[1]];
    }
}