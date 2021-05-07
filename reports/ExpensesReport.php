<?php

namespace Reports;

use Collectors\CalendarCollector;
use Reports\CalendarReport;

class ExpensesReport
{
    private $minDate = '';
    private $maxDate = '';

    public function __construct($minDate, $maxDate)
    {
        $this->minDate = $minDate;        
        $this->maxDate = $maxDate;        
    }

    public function create()
    {
        $collector = new CalendarCollector();
        $yearReport = new CalendarReport($this->minDate, $this->maxDate, $collector, ['no-days', 'no-weeks', 'month-no-zero-sums']);
        $yearReport->create();        
    }
}