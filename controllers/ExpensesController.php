<?php

namespace Controllers;

use Facades\Expense;
use Facades\Tlgr;
use Classes\DateCalc;
use Classes\MoneyFormat;
use Reports\ExpensesReport;

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
    //Отчёт расходы по неделям
    public function expensesReportWeeks($return = false)
    {
        $expensesReport = new ExpensesReport("2021-02-20", DateCalc::getToday());
        $expensesReport->chooseVariant("weeks");
        return p($expensesReport->create(), $return);
    }
    //Отчёт расходы по месяцам
    public function expensesReportMonths($return = false)
    {
        $expensesReport = new ExpensesReport("2021-02-20", DateCalc::getToday());
        $expensesReport->chooseVariant("months");
        return p($expensesReport->create(), $return);
    }
    public function expensesReportYear($return = false)
    {
        $sum = Expense::getPeriodSum([DateCalc::getCurrentYearFirstTime(), DateCalc::getCurrentTime()]);
        $sum = MoneyFormat::format($sum);
        return p($sum, $return);
    }
    //Смешанный отчёт по месяцам + неделям
    public function expensesReportMixed()
    {
        $text = "";
        $text .= "Расходы по неделям" . PHP_EOL;
        $text .= $this->expensesReportWeeks(true);
        $text .= "Расходы по месяцам" . PHP_EOL;
        $text .= $this->expensesReportMonths(true);
        $text .= "Расходы за год" . PHP_EOL;
        $text .= $this->expensesReportYear(true);
        p($text, false, true);
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