<?php

namespace Classes;

use \DateInterval;
use \DateTime;
use Facades\Tlgr;

class DateFilter
{
    private $inputText = '';
    private $processedText = '';
    private $filter = [];

    public function __construct($text)
    {
        $this->inputText = $text;
        $this->process();
    }
    private function process()
    {
        $dateFrom = "";
        $flag = false;
        if (!$flag && $this->setProcessedTextAndPeriod("сегодня")) {
            $dateFrom = date('Y-m-d 00:00:00');
            $flag = true;
        }
        if (!$flag && $this->setProcessedTextAndPeriod("неделя")) {
            $date = new DateTime();
            $date->sub(new DateInterval('P1W'));
            $dateFrom = $date->format('Y-m-d 00:00:00');
            $flag = true;
        }
        if (!$flag && $this->setProcessedTextAndPeriod("две недели")) {
            $date = new DateTime();
            $date->sub(new DateInterval('P2W'));
            $dateFrom = $date->format('Y-m-d 00:00:00');
            $flag = true;
        }
        // if (RegExp::search("две недели", $this->inputText)) {
        //     $dateFrom = date('Y-m-01');
        // }
        if ($dateFrom) {
            //Filter phrase found in text
            $this->filter = [$dateFrom, false];
            return true;
        } else {
            //Filter phrase not found in text
            return false;
        }
    }
    public function getPeriod()
    {
        return $this->filter;
    }
    public function getProcessedText()
    {
        return $this->processedText;
    }
    private function setProcessedTextAndPeriod($phrase)
    {
        // Tlgr::sendMessage("input text" . PHP_EOL . $this->inputText . PHP_EOL . $phrase);
        $text = $this->inputText;
        $count = 0;
        $this->processedText = trim(RegExp::replace("(.*)($phrase)(.*)", "$1$3", $text, $count));
        return boolval($count);
    }
}