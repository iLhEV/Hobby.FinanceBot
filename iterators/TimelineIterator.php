<?php

namespace Iterators;

use Classes\DateCalc;

//Класс отвечает за "перемещение" по дням календаря в направлении увеличения.
//Позволяет обращаться к текущему, предыдущему и следующему дню в рамках перемещения.
//В свою очередь по каждому из этих дней можно получить ряд параметров, 
//таких, как день недели, к которому принадлежит день, номер недели в году,
//номер или имя месяца и так далее.
class TimelineIterator
{
    private $iterator = "";
    private $prev = "";
    private $next = "";
    private $num = 1;
    private $limitDates = [];

    public function __construct($limitDates)
    {
        if ($limitDates[0] >= $limitDates[1]) {
            p("error maxDate should be greater than minDate");
        }
        $this->limitDates = $limitDates;
        $this->iterator = $this->limitDates[0];
        $this->next = $this->plusDate($this->iterator);
    }

    public function next()
    {
        //Начальное значение
        if (!$this->iterator) {
            $this->iterator = $this->limitDates[0];
        } else {
            //Наращиваю
            $this->prev = $this->iterator;
            $this->iterator = $this->plusDate($this->iterator);
            if (!$this->last()) {
                $this->next = $this->plusDate($this->iterator);
            } else {
                $this->next = false;
            }
        }
        $this->num++;
        return $this;
    }

    private function plusDate($date)
    {
        return date('Y-m-d', strtotime($date .' +1 day'));
    }

    public function getCurrent($resultType = "")
    {
        return $this->getResultByType($this->iterator, $resultType);
    }

    public function getPrev($resultType = "")
    {
        return $this->getResultByType($this->prev, $resultType);
    }

    public function getNext($resultType = "")
    {
        return $this->getResultByType($this->next, $resultType);
    }

    private function getResultByType($resultDate, $resultType)
    {
        switch ($resultType):
            case "month":
                return DateCalc::getMonth($resultDate);
                break;
            case "month_ru":
                return DateCalc::getMonthName(DateCalc::getMonth($resultDate));
                break;
            case "week":
                return DateCalc::getWeekNumberOfYear($resultDate);
            default:
                return $resultDate;
                break;
        endswitch;
    }

    public function last()
    {
        return $this->iterator === $this->limitDates[1];
    }

    public function nextIsLast()
    {
        return $this->prev === $this->limitDates[1];
    }

    public function wasStarted()
    {
        return $this->iterator !== $this->limitDates[0];
    }

    public function getNum()
    {
        return $this->num;
    }
}