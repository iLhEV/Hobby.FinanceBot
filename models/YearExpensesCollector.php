<?php

namespace Models;

class YearExpensesCollector
{
    private $weekSums = [];
    private $monthSums = [];
    public function __construct()
    {
    }

    public function addToWeek($weekNum, $val)
    {
        if (!isset($this->weekSums[$weekNum])) $this->weekSums[$weekNum] = 0;
        $this->weekSums[$weekNum] += $val;
    }

    public function addToMonth($monthNum, $val)
    {
        if (!isset($this->monthSums[$monthNum])) $this->monthSums[$monthNum] = 0;
        $this->monthSums[$monthNum] += $val;
    }

    public function getWeekSum($weekNum) {
        return $this->weekSums[$weekNum];
    }

    public function getMonthSum($monthNum) {
        return $this->monthSums[$monthNum];
    }
}