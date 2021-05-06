<?php

namespace Controllers;

use Facades\Expense;
use Facades\Tlgr;
use Classes\DateFilter;
use Classes\DateCalc;
use Models\YearExpensesReport;

class ExpensesController
{
    private $answerText = "";

    public function add($rule)
    {
        if (Expense::add($rule->foundMatches[0], $rule->foundMatches[1])) {
            Tlgr::sendMessage("Трата записана");
        } else {
            Tlgr::sendMessage("Ошибка записи траты в БД");
        }
        return true;
    }

    public function get($rule) {
        if (!$period = $rule->dateFilter->getPeriod()) {
            $period = [false, false];
        }
        $answer = "";
        $sum = 0;
        $query = Expense::getByDates($period[0], $period[1]);
        foreach($query as $item) {
            $str = "";
            $str .= "#" . $item['id'] . " " . date("d.m H:m", strtotime($item['created_at'])) . PHP_EOL;
            $str .= $item['name'] . PHP_EOL;
            $str .= $item['val'] . PHP_EOL;
            $sum += $item['val'];
            $str .= PHP_EOL;
            $answer .= $str;
        }
        $answer .= "Общая сумма: " . $sum;
        Tlgr::sendMessage($answer);
        return true;
    }
    
    public function getByCategories($text)
    {
        $date_from = false; $date_to = false;
        $cat_flag = false;
        if (preg_match("/(*UTF8)^категории\s([0-9]{1,2})\.([0-9]{1,2})$/ui", $text, $matches) || preg_match("/(*UTF8)^кат\s([0-9]{1,2})\.([0-9]{1,2})$/ui", $text, $matches)) {
            $cat_flag = true; $date_from = date("Y") . "-" .  $matches[2] . "-" .  $matches[1];
        } elseif (preg_match("/(*UTF8)^категории\s([0-9]{1,2})$/ui", $text, $matches) || preg_match("/(*UTF8)^кат\s([0-9]{1,2})$/ui", $text, $matches) ) {
            $cat_flag = true;
            $date_from = date("Y") . "-" .  (strlen($matches[1]) == 1 ? "0" . $matches[1] : $matches[1]) . "-01";
            $days_number = date('t', mktime(0, 0, 0, $matches[1], 1, date('Y')));
            $date_to = date("Y") . "-" .  (strlen($matches[1]) == 1 ? "0" . $matches[1] : $matches[1]) . "-" . $days_number;
        } elseif (($text == 'категории' || $text == 'кат' || $text == 'ка' || $text == 'свод' || $text == 'св' || $text == 'траты по категориям')) {
            $cat_flag = true; $date_from = date("Y-m-01");
        }

        if ($cat_flag) {
            $counters = Expense::getCategoriesCounters($date_from, $date_to);
            arsort($counters);
            $answer = ""; $sum_categories = 0;
            foreach ($counters as $category => $val) {
                $fval = $this->prepareNumber($val);
                $answer .= $category . ": " . $fval . PHP_EOL;
                $sum_categories += $val;
            }
            $sum = Expense::getSum($date_from, $date_to);
            $answer .= "не определена: " . ($sum - $sum_categories) . PHP_EOL;
            $answer .= "итого: " . $this->prepareNumber($sum) . PHP_EOL;
            Tlgr::sendMessage($answer);
            return true;
        }

        if ($text == 'категория не определена' || $text == 'не опр' || $text == 'неопр'  || $text == 'неоп') {
            $spendings = Expense::getSpendingsWithoutCategory();
            $answer = "";
            foreach ($spendings as $spending) {
                $answer .= $spending['name'] . PHP_EOL;
            }
            if ($answer == "") $answer = "Такие траты отсутствуют";
            Tlgr::sendMessage("Траты с категорией 'не определена':" . PHP_EOL . $answer);
            return true;
        }
    }
    private function prepareNumber($val)
    {
        return number_format($val, 0, '', ' ');
    }
    //Траты по временной шкале
    public function timeline()
    {
        $yearExpensesReport = new YearExpensesReport("2021-02-20", DateCalc::getToday());
        $yearExpensesReport->create();
        return;

        $this->collectAnswer('Сегодня: ');
        $this->collectAnswer(lcfirst(DateCalc::fetchMonthName(date("d.m.Y"))) . ", " . date('d'));
        $this->addEmptyStringToAnswer(2);
        $this->collectAnswer('Вот траты за этот и предыдущий месяцы:');
        $this->addEmptyStringToAnswer(2);
        $results = Expense::month();
        $supersum = 0;
        $i = 0;
        $previousMonthName = '';
        $weekSum = 0;
        $newMonthFlag = true;
        $weekNum = 0;
        foreach ($results as $date => $sum) {
            $monthName = DateCalc::fetchMonthName($date);
            $temestamp = strtotime($date);
            $dayOfWeekEng = date("l", $temestamp);
            //Копейки не в счёт
            $sum = $this->removeKopeiki($sum);
            //Перед понедельником вывожу дату
            if ($dayOfWeekEng === "Monday") {
                $this->collectAnswer("Итог: " . $weekSum);
                $this->addEmptyStringToAnswer();
                if ($newMonthFlag) {
                    $weekNum = 1;
                    $this->addEmptyStringToAnswer();
                    $this->collectAnswer("======== " . mb_strtoupper(DateCalc::fetchMonthName($date)) . " ========");
                    $newMonthFlag = false;
                    $previousMonthName = $monthName;
                } else {
                    $weekNum++;
                    if ($previousMonthName !== $monthName) {
                        $newMonthFlag = true;
                    }
                }
                $this->addEmptyStringToAnswer();
                $this->collectAnswer("::неделя " . $weekNum . "::");
                $this->addEmptyStringToAnswer();
                $weekSum = 0;
            }
            //Вывод и коплю итог
            $this->collectAnswer($this->showDaySpending($this->dayOfWeekToRussian($dayOfWeekEng, true), $this->formatSum(intval($sum))));
            $supersum += $sum;
            $weekSum += $sum;
            $i++;            
        }
        $this->collectAnswer(PHP_EOL);
        $this->collectAnswer($this->showDaySpending("Итог:", $this->formatSum($supersum)));
        $this->returnAnswer();
    }
    //Преобразование в русские дни недели
    public function dayOfWeekToRussian($dayOfWeekEng, $short = false)
    {
        $daysOfWeekTransformerLong = [
            'Sunday' => 'Воскресенье',
            'Monday' => 'Понедельник',
            'Tuesday' => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday' => 'Четверг',
            'Friday' => 'Пятница',
            'Saturday' => 'Суббота'
        ];
        $daysOfWeekTransformerShort = [
            'Sunday' => 'Вс',
            'Monday' => 'Пн',
            'Tuesday' => 'Вт',
            'Wednesday' => 'Ср',
            'Thursday' => 'Чт',
            'Friday' => 'Пт',
            'Saturday' => 'Сб'
        ];
        $short ? $daysOfWeekTransformer = $daysOfWeekTransformerShort : $daysOfWeekTransformer = $daysOfWeekTransformerLong;
        if (isset($daysOfWeekTransformer[$dayOfWeekEng])) {
            return $daysOfWeekTransformer[$dayOfWeekEng];
        } else {
            return $dayOfWeekEng;
        }
    }
    private function showDaySpending($day, $sum) {
        if ($sum == 0) {
            $sum = "-";
        } else {
            $sum = $sum . "р";
        }
        return $day . " " . $sum . PHP_EOL;
    }
    private function removeKopeiki($sum)
    {
        $sum_exploded = explode(".", $sum);
        if(count($sum_exploded) > 1) $sum = $sum_exploded[0];
        return $sum;
    }
    private function formatSum($sum)
    {
        return number_format($sum, 0, '.', ',');
    }
    private function collectAnswer($text)
    {
        $this->answerText .= $text;
    }
    private function addEmptyStringToAnswer($num = 1)
    {
        for ($i=1;$i<=$num;$i++) {
            $this->answerText .= PHP_EOL;
        }
    }
    private function returnAnswer()
    {
        Tlgr::sendMessage($this->answerText);
    }
}