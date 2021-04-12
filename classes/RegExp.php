<?php

namespace Classes;

class RegExp
{
    public static function resolve($pattern, $text)
    {
        $regs = explode(' ', $pattern);
        $pieces = explode(' ', $text);
        $matches = [];
        foreach ($regs as $i => $reg) {
            if(!isset($pieces[$i])) return false;
            $piece = $pieces[$i];
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
        if (preg_match('/string/', $reg)) {
            return 2;
        }
        if (preg_match('/amount/', $reg)) {
            return 3;
        }
    }
    private static function search($needle, $haystack)
    {
        return preg_match("/(*UTF8)$needle/ui", $haystack);
    }
}