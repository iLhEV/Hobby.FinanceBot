<?php

namespace Models;

use Facades\DB;

class Spending
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
                $res = DB::query("SELECT * FROM `spendings` WHERE `name` LIKE '%" . $word . "%'" . $dates_sql);
                //print_r("SELECT * FROM `spendings` WHERE `name` LIKE '%" . $word . "%'" . $dates_sql);
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
        $res = DB::query("SELECT sum(val) FROM `spendings`" . $dates_sql);
        return $res->fetchColumn();
    }
    public function getTotal()
    {
        $query = DB::query("SELECT sum(val) as summa FROM `spendings`");
        if (!$spending_sum = $query->fetchColumn()) $spending_sum = 0;
        return $spending_sum;
    }
    //Find spendings without defined category
    public function getSpendingsWithoutCategory()
    {
        $spendings_with_category = [];
        foreach (self::$categories as $category => $words) {
            foreach ($words as $word) {
                $res = DB::query("SELECT * FROM `spendings` WHERE `name` LIKE '%" . $word . "%'");
                foreach ($res as $spending) {
                    $spendings_with_category[$spending['id']] = $spending;
                }
            }
        }
        $res = DB::query("SELECT * FROM `spendings`");
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
        $query = DB::prepare("INSERT INTO `spendings` SET `name`=:name, `val`=:val");
        $query->execute($params);            
        if ( $query->rowCount()) {
            return DB::lastInsertId();
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
        print_r($where_sql);
        return DB::query("SELECT * FROM `spendings` " . $where_sql);
    }
}