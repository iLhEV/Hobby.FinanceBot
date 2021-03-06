<?php

function p($value = "")
{
    if (is_array($value)) {
        foreach ($value as $key => $val) {
            echo "[$key]" . PHP_EOL;
            print_r($val) . PHP_EOL . PHP_EOL . PHP_EOL;
        }
        return;
    }
    print_r($value);
    echo PHP_EOL;
}

function mb_ucfirst($string, $encoding)
{
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, null, $encoding);
    return mb_strtoupper($firstChar, $encoding) . $then;
}
