<?php

namespace Models;

class YearExpensesReport
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
        $collector = new YearExpensesCollector();
        $yearReport = new YearReport($this->minDate, $this->maxDate, $collector);
        $yearReport->create();        
    }
}