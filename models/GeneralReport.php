<?php

namespace Models;

use Classes\DateTransform;
use Classes\Timeline;
use Facades\DB;
use \DateInterval;
use \DateTime;
use \DatePeriod;

class GeneralReport
{
    private $list = [];
    private $startDate = "";
    private $stopDate = "";
    private $resultText = "";
    private $sumsByDays = [];

    public function __construct()
    {
    }

    public function create()
    {
        $timeline = new Timeline(['2021-02-12', '2021-03-01']);

        return;
        $this->sumsByDays = $this->getSumsByDays();
        $this->getSumsByWeeks();
    }

    public function setStartDate($date)
    {
        $this->startDate = $date;
    }

    public function setStopDate($date)
    {
        $this->stopDate = $date;
    }

    public function addToResultText($text)
    {
        $this->resultText .= $text;
    }

    //Подготавливает массив: в ключе дата в формате Y-m-d, в значении сумма трат за этот день
    public function getSumsByDays()
    {
        //Получаю из базы все траты после заданной даты
        $begin = DateTransform::getFirstDayOfPreviousMonth([date('m'), date('Y')]);
        $dateFrom = "{$begin[0]}.{$begin[1]}.{$begin[2]}";
        $spendings = DB::query("SELECT * FROM `spendings` WHERE `created_at` > '$dateFrom'");

        //Формирую массив, в котором в ключе указана дата в формате Y-m-d, а в значении сумма за эту дату
        $dates = [];
        foreach ($spendings as $spending) {
            $date = date_parse($spending['created_at']);
            $key = $date['year'] . "-" . DateTransform::addZero($date['month']) . "-" . DateTransform::addZero($date['day']);
            if (!isset($dates[$key])) {
                $dates[$key] = 0;
            } else {
                $dates[$key] += $spending['val'];
            }
        }

        //Формирую набор и если в этот день не было трат, то ставлю сумму ноль, это нужно, чтобы в отчёте присутствовали все дни.
        //А также округляю (удаляю) копейки.
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod(
            new DateTime($dateFrom),
            $interval,
            new DateTime(date('d.m.Y'))
        );
        foreach($daterange as $date) {
            $dateFormatted = $date->format('Y-m-d');
            if (!isset($dates[$dateFormatted])) {
                $dates[$dateFormatted] = 0;
            } else {
                $dates[$dateFormatted] = number_format($dates[$dateFormatted], 0, '.', '');
            }
        }

        //Сортирую полученный набор
        ksort($dates, SORT_NATURAL);

        //В конце просто возвращаю его, как результат функции
        return $dates;
    }
    
    //Подготавливает из массива "день - сумма" новый массив "неделя - сумма"
    public function getSumsByWeeks()
    {
        $weeks = [];
        foreach ($this->sumsByDays as $day => $sum) {
            $weekNum = DateTransform::getWeekNumberOfYearByDay($day);
            if (!isset($weeks[$weekNum])) {
                $weekTmp = &$weeks[$weekNum];
                $weekTmp = new WeekSpendings();
                $weekTmp->setYear(DateTransform::fetchYear($day));
                $weekTmp->setMonth(DateTransform::fetchMonthNumber($day));
                $weekTmp->setNumber($weekNum);
                //Теперь добавляю запись в общий список лет, месяцев, недель
                if (!isset($this->list[$weekTmp->getYear()])) {
                    $this->list[$weekTmp->getYear()] = [];
                } else {
                    if (!isset($this->list[$weekTmp->getYear()][$weekTmp->getMonth()])) {
                        $this->list[$weekTmp->getYear()][$weekTmp->getMonth()] = [];
                    } else {
                        if (!isset($this->list[$weekTmp->getYear()][$weekTmp->getMonth()][$weekTmp->getNumber()])) {
                            $this->list[$weekTmp->getYear()][$weekTmp->getMonth()][$weekTmp->getNumber()] = [];
                        } else {
                            //$this->list[$weekTmp->getYear()][$weekTmp->getMonth()][$weekTmp->getNumber()][$day] = true;
                        }
                    }
                }

            }
            $weekTmp->increaseSum($sum);
            
        }
        p($this->list);
        return;
        foreach($weeks as $week) {
            p($week->getNumber());
            p(DateTransform::getMonthNameByNum($week->getMonth()));
        }
    }
}