<?php

namespace Controllers;

use Facades\Spending;
use Facades\Tlgr;
use Classes\DateFilter;
class SpendingController
{
    public function add($rule)
    {
        if (Spending::add($rule->foundMatches[0], $rule->foundMatches[1])) {
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
        $query = Spending::getByDates($period[0], $period[1]);
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
            $counters = Spending::getCategoriesCounters($date_from, $date_to);
            arsort($counters);
            $answer = ""; $sum_categories = 0;
            foreach ($counters as $category => $val) {
                $fval = $this->prepareNumber($val);
                $answer .= $category . ": " . $fval . PHP_EOL;
                $sum_categories += $val;
            }
            $sum = Spending::getSum($date_from, $date_to);
            $answer .= "не определена: " . ($sum - $sum_categories) . PHP_EOL;
            $answer .= "итого: " . $this->prepareNumber($sum) . PHP_EOL;
            Tlgr::sendMessage($answer);
            return true;
        }

        if ($text == 'категория не определена' || $text == 'не опр' || $text == 'неопр'  || $text == 'неоп') {
            $spendings = Spending::getSpendingsWithoutCategory();
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
    //Траты недели
    public function week()
    {
        $results = Spending::week();
        $supersum = 0;
        foreach ($results as $date => $sum) {
            $temestamp = strtotime($date);
            $dayOfWeekEng = date("l", $temestamp);
            //Копейки не в счёт
            $sum = $this->removeKopeiki($sum);
            //Вывод и коплю
            $this->showDaySpending($this->dayOfWeekToRussian($dayOfWeekEng, true), $this->formatSum(intval($sum)));
            $supersum += $sum;
        }
        p();
        $this->showDaySpending("Итог:", $this->formatSum($supersum));
    }
    //Преобразовние в русские дни недели
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
        p($day . " " . $sum . 'р');
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
}