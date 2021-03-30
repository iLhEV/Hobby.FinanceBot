<?php

class SpendingController
{
    public function add($text)
    {
        if (preg_match('/(*UTF8)^([а-яёa-z\s\,0-9\-]+)\s([\+\-0-9\.]+)$/ui', $text, $matches)) {
            $name = $matches[1];
            $val = $matches[2];
            $params = [':name' => $name, ':val' => $val];
            $query = DB::prepare("INSERT INTO `spendings` SET `name`=:name, `val`=:val");
            $query->execute($params);            
            if ($query->rowCount()) {
                Tlgr::sendMessage("Трата записана");
            } else {
                Tlgr::sendMessage("Ошибка записи траты в БД");
            }
            return true;
        } else {
            return false;
        }
    }

    public function get($text) {
        $flag = false;
        $min_date_sql = "";
        $answer = "";
        $sum = 0;
        if ($text === "траты" || $text === "траты сегодня" || $text === "сегодня траты") {
            $min_date_sql = " WHERE created_at >= '" . date('Y-m-d') . "'";
            $flag = true;
        }
        if ($text === "траты неделя" || $text === "неделя траты" || $text === "траты за неделю") {
            $date = new DateTime(); $date->sub(new DateInterval('P1W'));
            $min_date_sql = " WHERE created_at >= '" . $date->format('Y-m-d') . "'";
            $flag = true;
        }
        if ($text === "траты две недели" || $text === "траты 2 недели" || $text === "траты за две недели") {
            $date = new DateTime(); $date->sub(new DateInterval('P2W'));
            $min_date_sql = " WHERE created_at >= '" . $date->format('Y-m-d') . "'";
            $flag = true;
        }
        if ($text === "все траты" || $text === "траты все") {
            $flag = true;
        }
        if ($flag) {
            $query = DB::query("SELECT * FROM `spendings`" . $min_date_sql);
            foreach($query as $item) {
                $answer .= "#" . $item['id'] . " " . date("d.m H:m", strtotime($item['created_at'])) . PHP_EOL;
                $answer .= $item['name'] . PHP_EOL;
                $answer .= $item['val'] . PHP_EOL;
                $sum += $item['val'];
                $answer .= PHP_EOL;
            }
            $answer .= "Общая сумма: " . $sum;
            Tlgr::sendMessage($answer);
            return true;
        }
        return false;
    }
    
    public function getByCategories($text)
    {
        if ($text === 'категории') {
            print_r($counters);
            $answer = ""; $sum_categories = 0;
            foreach ($counters as $category => $val) {
                $answer .= $category . ":" . $val . PHP_EOL;
                $sum_categories += $val;
            }
            $st = DB::query("SELECT sum(val) FROM `spendings`");
            $sum = $st->fetchColumn();
            $answer .= "не определена: " . ($sum - $sum_categories) . PHP_EOL;
            Tlgr::sendMessage($answer);
            return true;
        }
    }

}