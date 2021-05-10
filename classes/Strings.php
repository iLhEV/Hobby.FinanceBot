<?php

namespace Classes;

class Strings
{
    //Создаёт строку из указанных символов заданной длины
    public static function repeatFragmentToSpecifiedLength($fragment, $length)
    {
        $resultString = "";
        $resultLength = 0;
        while ($resultLength < $length) {
            $resultString .= $fragment;
            $resultLength = mb_strlen($resultString);
        }
        //Подрезаю, если превышена нужная длина
        return substr($resultString, 0, $length -1);
    }

    //Дополняет строку до заданной длины фрагментом текста
    public static function growStringToSpecifiedLength($string, $fragment, $goalLength, $textAlign = "left")
    {
        $additionalLength = $goalLength - mb_strlen($string);
        if ($additionalLength) {
            $additionalString = Strings::repeatFragmentToSpecifiedLength($fragment, $additionalLength);
        } else {
            $additionalString = "";
        }
        if ($textAlign === "right") {
            
            return $additionalString . $string;
        } else {
            return $string . $additionalString;
        }
    }
}