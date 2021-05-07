<?php

namespace Collectors;

use Classes\DateCalc;
use Facades\Expense;

class CalendarCollector
{
    private $weekSums = [];
    private $monthSums = [];

    public function __construct()
    {
        //Считаю суммы по месяцам
        for ($monthNum = 1; $monthNum <= 12; $monthNum++) {
            $sum = Expense::getPeriodSum([DateCalc::getCurrentYearMonthFirstTime($monthNum), DateCalc::getCurrentYearMonthLastTime($monthNum)]);
            $this->addToMonthValue($monthNum, $sum);
        }
        //Считаю суммы по неделям
        for ($weekNum = 1; $weekNum <= DateCalc::getCurrentYearWeekNumber(); $weekNum++) {
            $sum = Expense::getPeriodSum(DateCalc::getFirstAndLastTimeOfWeekForCurrentYear($weekNum));
            $this->addToWeekValue($weekNum, $sum);
        }
    }

    public function addToWeekValue($weekNum, $val)
    {
        if (!isset($this->weekSums[$weekNum])) $this->weekSums[$weekNum] = 0;
        $this->weekSums[$weekNum] += $val;
    }

    public function addToMonthValue($monthNum, $val)
    {
        if (!isset($this->monthSums[$monthNum])) $this->monthSums[$monthNum] = 0;
        $this->monthSums[$monthNum] += $val;
    }

    public function getWeekValue($weekNum) {
        if (isset($this->weekSums[$weekNum])) {
            return $this->weekSums[$weekNum];
        } else {
            return null;
        }
    }

    public function getMonthValue($monthNum) {
        if (isset($this->monthSums[$monthNum])) {
            return $this->monthSums[$monthNum];
        } else {
            return null;
        }
    }

    public function getAllWeeksValues()
    {
        return $this->weekSums;
    }

    public function getAllMonthsValues()
    {
        return $this->monthSums;
    }
}