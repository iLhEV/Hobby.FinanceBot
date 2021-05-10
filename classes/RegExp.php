<?php

namespace Classes;

class RegExp
{
    public static function resolve($pattern, $text)
    {
        $regs = explode(' ', $pattern);
        $pieces = explode(' ', $text);
        $matches = [];
        $diff = count($pieces) - count($regs);
        if ($diff < 0) return self::generateError('Количество слов в haystack меньше количества участков в regexp');
        $cursor = -1;
        foreach ($regs as $reg) {
            $cursor++;
            if(!isset($pieces[$cursor])) return false;
            $piece = $pieces[$cursor];
            switch (self::getType($reg)):
                case 1:
                    if (array_search($piece, explode('|', $reg)) !== false) {
                        continue 2;
                    } else {
                        //Тип 1 является "проверяющим". Если входное значение не равно ни одному из вариантов, то регулярное выражение не удовлетворено.
                        return false;
                    }
                break;
                case 2:
                    if (self::search('^[а-яёa-z\s]+$', $piece)) {
                        $matches[] = $piece;
                        continue 2;
                    } else {
                        return false;
                    }
                break;
                case 3:
                    if (self::search('^([0-9]+\.{1}[0-9]+)|([0-9]+)$', $piece)) {
                        $matches[] = $piece;
                        continue 2;
                    } else {
                        return false;
                    }
                break;
                case 4:
                    $pieces_collection = '';
                    $shift = 0;
                    while ($shift <= $diff) {
                        $pieces_collection .= $pieces[$cursor] . " ";
                        $cursor++;
                        $shift++;
                    }
                    $cursor--;
                    $matches[] = rtrim($pieces_collection);
                    continue 2;
                break;
            endswitch;
        }
        if (count($matches)) return $matches;
        return true;
    }
    private static function getType($reg)
    {
        if (preg_match('/\|/', $reg)) {
            return 1;
        }
        if (preg_match('/word/', $reg)) {
            return 2;
        }
        if (preg_match('/amount/', $reg)) {
            return 3;
        }
        if (preg_match('/string/', $reg)) {
            return 4;
        }
        if (preg_match('/year\-month/', $reg)) {
            return 5;
        }
    }
    public static function search($phrase, $text)
    {
        return preg_match("/(*UTF8)$phrase/ui", $text);
    }
    private static function generateError($message)
    {
        //write something here in future
        return false;
    }
    public static function replace($phrase, $replacement, $text, &$count, $limit = -1)
    {
        return preg_replace("/(*UTF8)$phrase/ui", $replacement, $text, $limit, $count);
    }
}