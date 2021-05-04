<?php

namespace Classes;

class Timeline
{
    public function __construct($interval)
    {
        $this->create($interval);
    }

    private function create($interval)
    {
        $date_start = $interval[0];
        $date_finish = $interval[1];
        if ($date_finish < $date_start) {
            p('error interval is incorrect');
            return;
        }
        $stop_flag = false;
        $curr_date = $date_start;
        while (!$stop_flag) {
            //do something
            p($curr_date);
            $curr_date = date('Y-m-d', strtotime($curr_date .' +1 day'));
            if ($curr_date > $date_finish) $stop_flag = true;
        }
    }
}