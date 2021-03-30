<?php

class Spending
{
    //This function collect different information about spendings by categories
    private function categories()
    {
        //Search words to define category
        $categories = [
            'здоровье' => ['узи', 'уролог', 'антибиотик', 'микро-кинезио'],
            'транспорт' => ['маршрутка', 'электричка', 'автобус', 'такси'],
            'дети' => ['алименты'],
            'продукты' => ['мармелад', 'чурчхела', 'халва', 'зелень', 'масло', 'пахлава'],
        ];
        //Set counters to zero
        foreach ($categories as $category => $words) {
            $counters[$category] = 0;
        }
        foreach ($categories as $category => $words) {
            foreach ($words as $word) {
                $st = DB::query("SELECT * FROM `spendings` WHERE `name` LIKE '%" . $word . "%'");
                foreach ($st as $spending) {
                    $counters[$category] += $spending['val'];
                }
            }
        }
   }
}