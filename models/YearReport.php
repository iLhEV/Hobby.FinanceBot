<?php

namespace Models;

use Classes\DateCalc;
use Classes\TimelineIterator;
use Facades\DB;
use \DateInterval;
use \DateTime;
use \DatePeriod;
use Classes\MoneyFormat;

class YearReport
{
    private $list = [];
    private $minDate = "";
    private $maxDate = "";
    private $resultText = "";
    private $sumsByDays = [];
    private $collector = null;
    //data comes from outside to output as value
    //keys are dates in "Y-m-d" format
    private $config;

    public function __construct($minDate, $maxDate, $collector = null, $config)
    {
        $this->setMinDate($minDate);
        $this->setMaxDate($maxDate);
        $this->collector = $collector;
        $this->config = $config;
    }

    public function create()
    {
        $iterator = new TimelineIterator([$this->minDate, $this->maxDate]);
        
        while(!$iterator->nextIsLast()) {
            if ($iterator->getNum() === 1 &&  !$this->inConfig('no-weeks')) {
                $this->printWeekNumber($iterator->getNext('week'), $this->getCollectorWeekValue($iterator->getNext('week')));
            }
            if (($iterator->getPrev()
                && $iterator->getCurrent('month') !== $iterator->getPrev('month'))
                || $iterator->getNum() === 1
            ) {
                if ($this->inConfig('month-no-zero-sums') && !$this->getCollectorMonthValue($iterator->getCurrent('month'))) {
                    //Месяцы с нулевыми суммами не выводим
                } else {
                    $this->printDayNameWithMonthLabel(
                        $iterator->getCurrent(),
                        $iterator->getCurrent('month_ru'),
                        $this->getCollectorMonthValue($iterator->getCurrent('month'))
                    );
                }
            } else {
                if (!$this->inConfig('no-days')) $this->printDayName($iterator->getCurrent());
            }
            if ($iterator->getNext()) {
                if ($iterator->getCurrent('month') !== $iterator->getNext('month')) {
                    //p($iterator->getNext('month_ru'));
                }
                if ($iterator->getCurrent('week') !== $iterator->getNext('week') && !$this->inConfig('no-weeks')) {
                    $this->printWeekNumber($iterator->getNext('week'), $this->getCollectorWeekValue($iterator->getNext('week')));
                }
            }
            //p("---");            
            $iterator = $iterator->next();
        }
        $this->printResult();
        return;
    }

    private function inConfig($param)
    {
        return in_array($param, $this->config);
    }

    public function getCollectorWeekValue($weekNum)
    {
        return $this->collector ? $this->collector->getWeekValue($weekNum) : null;
    }

    public function getCollectorMonthValue($monthNum)
    {
        return $this->collector ? $this->collector->getMonthValue($monthNum) : null;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    //Print-функции в этом классе работают в режиме эмулятора.
    //Хоть они и называются, начиная с 'print', однако они лишь добавляют вывод
    //к результирующему тексту, но не делают вывод напрямую.
    private function printWeekNumber($name, $value = null)
    {
        $this->addToResult($name . "нед   " . MoneyFormat::format($value) . PHP_EOL);
    }

    private function printDayName($name)
    {
        $this->addToResult("    " . $name . PHP_EOL);
    }

    private function printDayNameWithMonthLabel($day, $month, $value = null)
    {
        $this->addToResult("    " . $day . "   <-- " . $month . "   " . MoneyFormat::format($value) . PHP_EOL);
    }

    public function setMinDate($date)
    {
        $this->minDate = $date;
    }

    public function setMaxDate($date)
    {
        $this->maxDate = $date;
    }

    public function addToResult($text)
    {
        $this->resultText .= $text;
    }

    public function printResult()
    {
        p($this->resultText);
    }
}