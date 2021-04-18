<?php

namespace Classes;

use \DateInterval;
use \DateTime;

class DateFilter
{
    private $inputText = '';
    private $processedText = '';
    private $result = [];

    public function __construct($text)
    {
        $this->inputText = $text;
        $this->process();
    }
    private function process()
    {
        $dateFrom = "";
        if ($this->searchAndReplace("сегодня")) {
            $dateFrom = date('Y-m-d');
        }
        if ($this->searchAndReplace("неделя")) {
            $date = new DateTime();
            $date->sub(new DateInterval('P1W'));
            $dateFrom = $date->format('Y-m-d');
        }
        if (RegExp::search("две недели", $this->inputText)) {
            $date = new DateTime(); $date->sub(new DateInterval('P2W'));
            $dateFrom = $date->format('Y-m-d');
        }
        if (RegExp::search("две недели", $this->inputText)) {
            $dateFrom = date('Y-m-01');
        }
        if ($dateFrom) {
            //Filter phrase found in text
            $this->result = [$dateFrom, false];
            return true;
        } else {
            //Filter phrase not found in text
            return false;
        }
    }
    public function getResult()
    {
        return $this->result;
    }
    public function getProcessedText()
    {
        return $this->processedText;
    }
    private function searchAndReplace($phrase)
    {
        $text = $this->inputText;
        $count = 0;
        $this->processedText = RegExp::replace("(.*)($phrase)(.*)", "$1$3", $text, $count);
        return boolval($count);
    }
}