<?php

namespace Classes;

class Timeline
{
    private $interval;

    public function __construct($interval)
    {
        $this->interval = $interval;
    }

    public function createArray()
    {
        $date_start = $this->interval[0];
        $date_finish = $this->interval[1];
        if ($date_finish < $date_start) {
            p('error interval is incorrect');
            return;
        }
        $stop_flag = false;
        $loop_number = 1;
        $curr_date = $date_start;
        $timeline = [];
        while (!$stop_flag) {
            //Разделяю дату
            $year = intval(date("Y", strtotime($curr_date)));
            $month = intval(date("m", strtotime($curr_date)));
            $day = intval(date("d", strtotime($curr_date)));
            //Создаю временную шкалу
            $timeline[$year][$month][$day] = $curr_date;
            //Если целевой день достигнут, то останавливаюсь
            if ($curr_date === $date_finish) $stop_flag = true;
            //Прибавляю день и иду дальше
            $curr_date = date('Y-m-d', strtotime($curr_date .' +1 day'));
        }
        return $timeline;
    }
}