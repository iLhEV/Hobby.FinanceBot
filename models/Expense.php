<?php

namespace Models;

use Facades\DB as PDOW;
use \DateInterval;
use \DateTime;
use \DatePeriod;
use Classes\DateCalc;

class Expense
{
    //Search words to define category
    public static $categories = [
        'здоровье' => ['узи', 'уролог', 'антибиотик', 'микро-кинезио', 'йога', 'психотерапевт'],
        'аренда жилья' => ['квартира'],
        'транспорт' => ['маршрутка', 'электричка', 'автобус', 'такси'],
        'дети' => ['алименты'],
        'продукты' => ['зелень', 'масло', 'пахлава', 'продукты', 'мед', 'рыба', 'калбаса'],
        'вкусняшки' => ['мармелад', 'чурчхела', 'халва', 'сгущенка', 'печенье'],
        'кафе' => ['кафе'],
        'подарки' => ['Тане', 'цветы'],
        'подношения' => ['подношение'],
        'связь' => ['связь'],
        'дом/быт/одежда' => ['салатник', 'дождевик', 'лампочки', 'магнит косметик', 'быт', 'скотч', 'фильтр для воды', 'мешки для пылесоса'],
        'техника' => ['монитор']
    ];

    //This function collect different information about spendings by categories
    public function getCategoriesCounters($date_from = false, $date_to = false)
    {
        $dates_sql = "";
        if ($date_from) $dates_sql .= " AND `created_at` >= '" . $date_from . "'";
        if ($date_to) $dates_sql .= " AND `created_at` <= '" . $date_to . "'";
        //Set counters to zero
        foreach (self::$categories as $category => $words) {
            $counters[$category] = 0;
        }
        $found = [];
        foreach (self::$categories as $category => $words) {
            foreach ($words as $word) {
                $res = PDOW::query("SELECT * FROM `expenses` WHERE `name` LIKE '%" . $word . "%'" . $dates_sql);
                //print_r("SELECT * FROM `expenses` WHERE `name` LIKE '%" . $word . "%'" . $dates_sql);
                if ($res->rowCount()) {
                    foreach ($res as $spending) {
                        if (!isset($found[$spending['id']])) {
                            $counters[$category] += $spending['val'];
                            $found[$spending['id']] = true;
                        }
                    }
                }
            }
        }
        return $counters;
    }
    //Get all spendings by date
    public function getSum($date_from = false, $date_to = false)
    {
        $dates_sql = "";
        if ($date_from) $dates_sql .= "WHERE `created_at` >= '" . $date_from . "'";
        if ($date_to) $dates_sql .= " AND `created_at` <= '" . $date_to . "'";
        $res = PDOW::query("SELECT sum(val) FROM `expenses`" . $dates_sql);
        return $res->fetchColumn();
    }
    public function getTotal()
    {
        $query = PDOW::query("SELECT sum(val) as summa FROM `expenses`");
        if (!$spending_sum = $query->fetchColumn()) $spending_sum = 0;
        return $spending_sum;
    }
    //Find spendings without defined category
    public function getSpendingsWithoutCategory()
    {
        $spendings_with_category = [];
        foreach (self::$categories as $category => $words) {
            foreach ($words as $word) {
                $res = PDOW::query("SELECT * FROM `expenses` WHERE `name` LIKE '%" . $word . "%'");
                foreach ($res as $spending) {
                    $spendings_with_category[$spending['id']] = $spending;
                }
            }
        }
        $res = PDOW::query("SELECT * FROM `expenses`");
        $spendings_without_category = [];
        foreach ($res as $spending) {
            if (!isset($spendings_with_category[$spending['id']])) $spendings_without_category[$spending['id']] = $spending;
        }

        return $spendings_without_category;
    }
    //Add spending model
    public function add($name, $val)
    {
        $params = [':name' => $name, ':val' => $val];
        $query = PDOW::prepare("INSERT INTO `expenses` SET `name`=:name, `val`=:val");
        $query->execute($params);            
        if ($query->rowCount()) {
            return PDOW::lastInsertId();
        } else {
            return false;
        }
    }
    //Get by dates
    public function getByDates($date_from = false, $date_to = false)
    {
        $where_sql = "";
        if ($date_from) $where_sql = "created_at >= '" . $date_from . "'";
        if ($where_sql && $date_to) $where_sql .= " AND ";
        if ($date_to) $where_sql .= "created_at <= '" . $date_to . "'";
        if ($where_sql) $where_sql = " WHERE " . $where_sql;
        return PDOW::query("SELECT * FROM `expenses` " . $where_sql);
    }

    public function getPeriodSum($period)
    {
        $minDate = $period[0];
        $maxDate = $period[1];
        $params = [':minDate' => $minDate, ':maxDate' => $maxDate];
        $query = PDOW::prepare("SELECT sum(`val`) FROM `expenses` WHERE `created_at`>=:minDate AND `created_at`<=:maxDate");
        $query->execute($params);            
        return intval($query->fetchColumn());
    }

    public function month()
    {
        $begin = DateCalc::getFirstDayOfPreviousMonth([date('m'), date('Y')]);
        $dateFrom = "{$begin[0]}.{$begin[1]}.{$begin[2]}";
        $spendings = PDOW::query("SELECT * FROM `expenses` WHERE `created_at` > '$dateFrom'");
        $dates = [];
        //Прохожу по тратам
        foreach ($spendings as $spending) {
            $date = date_parse($spending['created_at']);
            $key = $date['year'] . "-" . DateCalc::addZero($date['month']) . "-" . DateCalc::addZero($date['day']);
            if (!isset($dates[$key])) {
                $dates[$key] = 0;
            } else {
                $dates[$key] += $spending['val'];
            }
        }
        //Формирую набор и если в этот день не было трат, то ставлю сумму ноль
        $interval = new DateInterval('P1D');
        
        $daterange = new DatePeriod(
            new DateTime($dateFrom),
            $interval,
            new DateTime(date('d.m.Y'))
        );
        foreach($daterange as $date) {
            // $dateTemp = new DateTime();
            // $dateTemp->sub(new DateInterval('P' . $i . 'D'));
            // $dateTemp = $dateTemp->format('Y-m-d');
            $dateFormatted = $date->format('Y-m-d');
            if (!isset($dates[$dateFormatted])) {
                $dates[$dateFormatted] = 0;
            } 
        }
        ksort($dates, SORT_NATURAL);
        return $dates;
    }
}