<?php
class TlgrClient
{
    public $api = "https://api.telegram.org/bot1722248171:AAGJPqhLEsHn_oYx9ldGbaYR68vu7NmVrG8/sendMessage?chat_id=1349171752&";

    public function sendMessage($text)
    {
        if ($GLOBALS['http_answer']) {
            p($text);
        } else {
            $text_params = http_build_query( ['text' => $text] );
            file_get_contents($this->api . $text_params);
        }
    }
}