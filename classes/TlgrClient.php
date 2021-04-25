<?php

namespace Classes;

class TlgrClient
{
    public $api;
    private $maxTextLength = 4000;

    public function __construct()
    {
        $this->api = "https://api.telegram.org/bot" . $GLOBALS['env']['bot_key'] . "/sendMessage?chat_id=1349171752&";
    }
    public function sendMessage($text)
    {
        if ($GLOBALS['http_answer']) {
            p($text);
        } else {
            if (strlen($text) > $this->maxTextLength) {
                $text = substr($text, 0, $this->maxTextLength);
                $text .= PHP_EOL . PHP_EOL; 
                $text .= "!!! Текст был обрезан до максимальной длины в " . $this->maxTextLength . " символов";
            }
            $text_params = http_build_query( ['text' => $text] );
            file_get_contents($this->api . $text_params);
        }
    }
}