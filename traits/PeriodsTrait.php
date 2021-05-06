<?php

namespace Traits;

use \DateTime;

trait PeriodsTrait
{
    //Дата последнего дня заданного месяца и года
    public static function getMonthLastDay($date)
    {
        return date("Y-m-t", strtotime($date));
    }

    //Дата последнего дня заданного месяца в текущем году
    public static function getCurrentYearMonthLastDate($monthNum)
    {
        return self::getMonthLastDay(date("Y-") . $monthNum);
    }

    //Дата и последняя секунда последнего дня заданного месяца в текущем году
    public static function getCurrentYearMonthLastTime($monthNum)
    {
        return self::getCurrentYearMonthLastDate($monthNum) . " 23:59:59";
    }

    //Дата первого дня заданного месяца и года
    public static function getMonthFirstDay($date)
    {
        return date("Y-m-01", strtotime($date));
    }

    //Дата первого дня заданного месяца в текущем году
    public static function getCurrentYearMonthFirstDate($monthNum)
    {
        return self::getMonthFirstDay(date("Y-") . $monthNum . "-01");
    }

    //Дата с первой секунды первого дня заданного месяца в текущем году
    public static function getCurrentYearMonthFirstTime($monthNum)
    {
        return self::getCurrentYearMonthFirstDate($monthNum) . " 00:00:00";
    }

    //Дата первого и последнего дня недели по номеру недели и году
    public static function getFirstAndLastDayOfWeek($weekNum, $yearNum) {
        $dto = new DateTime();
        $dto->setISODate($yearNum, $weekNum);
        $period[] = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $period[] = $dto->format('Y-m-d');
        return $period;        
    }

    //То же, что и getFirstAndLastDayOfWeek, но для текущего года
    public static function getFirstAndLastDayOfWeekForCurrentYear($weekNum) {
        return self::getFirstAndLastDayOfWeek($weekNum, date("Y"));
    }
    //То же, что и getFirstAndLastDayOfWeekForCurrentYear, но к датам добавляется начальное и конечное время
    public static function getFirstAndLastTimeOfWeekForCurrentYear($weekNum) {
        $period = self::getFirstAndLastDayOfWeekForCurrentYear($weekNum);
        return [$period[0] . " 00:00:00", $period[1]. " 23:59:59"];
    }
}
