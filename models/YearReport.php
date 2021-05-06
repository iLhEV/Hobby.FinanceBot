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
    private $data = []; 

    public function __construct($minDate, $maxDate, $collector = null)
    {
        $this->setMinDate($minDate);
        $this->setMaxDate($maxDate);
        $this->collector = $collector;
    }

    public function create()
    {
        $iterator = new TimelineIterator([$this->minDate, $this->maxDate]);
        
        while(!$iterator->nextIsLast()) {
            //p("---");                        
            if ($iterator->getNum() === 1) {
                $this->printWeekNumber($iterator->getNext('week'), $this->getCollectorWeekValue($iterator->getNext('week')));
            }
            if (($iterator->getPrev()
                && $iterator->getCurrent('month') !== $iterator->getPrev('month'))
                || $iterator->getNum() === 1
            ) {
                $this->printDayNameWithMonthLabel($iterator->getCurrent(), $iterator->getCurrent('month_ru'), $this->getCollectorMonthValue($iterator->getCurrent('month')));
            } else {
                $this->printDayName($iterator->getCurrent());
            }
            if ($iterator->getNext()) {
                if ($iterator->getCurrent('month') !== $iterator->getNext('month')) {
                    //p($iterator->getNext('month_ru'));
                }
                if ($iterator->getCurrent('week') !== $iterator->getNext('week')) {
                    $this->printWeekNumber($iterator->getNext('week'), $this->getCollectorWeekValue($iterator->getNext('week')));
                }
            }
            //p("---");            
            $iterator = $iterator->next();
        }
        $this->printResult();
        return;
        $this->sumsByDays = $this->getSumsByDays();
        $this->getSumsByWeeks();

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

    //Подготавливает массив: в ключе дата в формате Y-m-d, в значении сумма трат за этот день
    public function getSumsByDays()
    {
        //Получаю из базы все траты после заданной даты
        $begin = DateCalc::getFirstDayOfPreviousMonth([date('m'), date('Y')]);
        $dateFrom = "{$begin[0]}.{$begin[1]}.{$begin[2]}";
        $spendings = DB::query("SELECT * FROM `spendings` WHERE `created_at` > '$dateFrom'");

        //Формирую массив, в котором в ключе указана дата в формате Y-m-d, а в значении сумма за эту дату
        $dates = [];
        foreach ($spendings as $spending) {
            $date = date_parse($spending['created_at']);
            $key = $date['year'] . "-" . DateCalc::addZero($date['month']) . "-" . DateCalc::addZero($date['day']);
            if (!isset($dates[$key])) {
                $dates[$key] = 0;
            } else {
                $dates[$key] += $spending['val'];
            }
        }

        //Формирую набор и если в этот день не было трат, то ставлю сумму ноль, это нужно, чтобы в отчёте присутствовали все дни.
        //А также округляю (удаляю) копейки.
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod(
            new DateTime($dateFrom),
            $interval,
            new DateTime(date('d.m.Y'))
        );
        foreach($daterange as $date) {
            $dateFormatted = $date->format('Y-m-d');
            if (!isset($dates[$dateFormatted])) {
                $dates[$dateFormatted] = 0;
            } else {
                $dates[$dateFormatted] = number_format($dates[$dateFormatted], 0, '.', '');
            }
        }

        //Сортирую полученный набор
        ksort($dates, SORT_NATURAL);

        //В конце просто возвращаю его, как результат функции
        return $dates;
    }
    
    //Подготавливает из массива "день - сумма" новый массив "неделя - сумма"
    public function getSumsByWeeks()
    {
        $weeks = [];
        foreach ($this->sumsByDays as $day => $sum) {
            $weekNum = DateCalc::getWeekNumberOfYear($day);
            if (!isset($weeks[$weekNum])) {
                $weekTmp = &$weeks[$weekNum];
                $weekTmp = new WeekSpendings();
                $weekTmp->setYear(DateCalc::fetchYear($day));
                $weekTmp->setMonth(DateCalc::fetchMonthNumber($day));
                $weekTmp->setNumber($weekNum);
                //Теперь добавляю запись в общий список лет, месяцев, недель
                if (!isset($this->list[$weekTmp->getYear()])) {
                    $this->list[$weekTmp->getYear()] = [];
                } else {
                    if (!isset($this->list[$weekTmp->getYear()][$weekTmp->getMonth()])) {
                        $this->list[$weekTmp->getYear()][$weekTmp->getMonth()] = [];
                    } else {
                        if (!isset($this->list[$weekTmp->getYear()][$weekTmp->getMonth()][$weekTmp->getNumber()])) {
                            $this->list[$weekTmp->getYear()][$weekTmp->getMonth()][$weekTmp->getNumber()] = [];
                        } else {
                            //$this->list[$weekTmp->getYear()][$weekTmp->getMonth()][$weekTmp->getNumber()][$day] = true;
                        }
                    }
                }

            }
            $weekTmp->increaseSum($sum);
            
        }
        p($this->list);
        return;
        foreach($weeks as $week) {
            p($week->getNumber());
            p(DateCalc::getMonthName($week->getMonth()));
        }
    }
}