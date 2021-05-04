<?php

namespace Classes;

class TimelineIterator
{
    private $iterator = "";
    private $prev = "";
    private $next = "";
    private $num = 1;
    private $limitDates = [];

    public function __construct($limitDates)
    {
        if ($limitDates[0] <= $limitDates[1]) {
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

    public function getCurrent()
    {
        return $this->iterator;
    }

    public function getPrev()
    {
        return $this->prev;
    }

    public function getNext()
    {
        return $this->next;
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
}