<?php

namespace Classes;

class Timeline
{
    private $timeline = ['call create and get before'];
    private $iterator = false;
    public function __construct($interval)
    {
        $this->interval = $interval;
    }

    public function create($interval)
    {
        $date_start = $interval[0];
        $date_finish = $interval[1];
        if ($date_finish < $date_start) {
            p('error interval is incorrect');
            return;
        }
        $stop_flag = false;
        $loop_number = 1;
        $curr_date = $date_start;
        while (!$stop_flag) {
            //Разделяю дату
            $year = intval(date("Y", strtotime($curr_date)));
            $month = intval(date("m", strtotime($curr_date)));
            $day = intval(date("d", strtotime($curr_date)));
            ////Определяю начальное значение итератора
            ////if ($loop_number === 1) {
                ////$this->iterator = [$year, $month, $day];
            ////}
            //Создаю временную шкалу
            $this->timeline[$year][$month][$day] = $curr_date;
            //Если целевой день достигнут, то останавливаюсь
            if ($curr_date === $date_finish) $stop_flag = true;
            //Прибавляю день и иду дальше
            $curr_date = date('Y-m-d', strtotime($curr_date .' +1 day'));
        }
        return $this->timeline;
    }

    public function next()
    {
        //Начальное значение
        if (!$this->iterator) {
            $this->iterator = $this->interval[0];
        } else {
            //Наращиваю
            $this->iterator = date('Y-m-d', strtotime($this->iterator .' +1 day'));
        }
        return $this;
    }

    public function getIterator()
    {
        return $this->iterator;
    }

    public function iteratorWasStopped()
    {
        return $this->iterator === $this->interval[1];
    }

    public function iteratorWasStarted()
    {
        return $this->iterator !== $this->interval[0];
    }
}