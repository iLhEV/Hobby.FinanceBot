<?php

namespace Reports;

use Collectors\CalendarCollector;
use Reports\CalendarReport;

class ExpensesReport
{
    private $minDate = '';
    private $maxDate = '';
    private $config = [];

    public function __construct($minDate, $maxDate)
    {
        $this->minDate = $minDate;        
        $this->maxDate = $maxDate;        
    }

    public function create()
    {
        $collector = new CalendarCollector();
        $yearReport = new CalendarReport($this->minDate, $this->maxDate, $collector, $this->config);
        return $yearReport->create();        
    }

    public function chooseVariant($variant)
    {
        switch ($variant):
            case "weeks":
                $this->config = ['no-days', 'no-months', 'no-weeks-zero-values', 'week-interval-label'];
            break;
            case "months":
                $this->config = ['no-days', 'no-weeks', 'no-months-zero-values', 'month-no-day'];
            break;
        endswitch;
    }
}