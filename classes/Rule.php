<?php

namespace Classes;
use Classes\RegExp;

class Rule
{
    private $name = '';
    private $exactMatches = [];
    private $patternMatches = [];
    private $controller ='';
    private $method = '';
    private $foundMatches = [];

    public function __construct($name)
    {
        $this->name = $name;
    }
    public function addExactMatches($phrases)
    {
        foreach ($phrases as $phrase) {
            if (!in_array($phrase, $this->exactMatches)) $this->exactMatches[] = $phrase;
        }
        return true;
    }
    public function addPatternMatch($pattern)
    {
        $this->patternMatches[] = $pattern;
    }
    public function addResolution($controller, $method)
    {
        $this->controller = "Controllers\\" . $controller;
        $this->method = $method;
        return true;
    }
    public function trigger()
    {
        $obj = new $this->controller();
        count($this->foundMatches) ? $input_array = $this->foundMatches : $input_array = [];
        $obj->{$this->method}($input_array);
        return true;
    }
    //Present example of text in call
    public function example($text)
    {
        return true;
    }
    public function resolve($text)
    {
        $text = trim($text);
        
        //Сначала проверка на точные совпадения
        foreach ($this->exactMatches as $match) {
            if ($text === $match) return true;
        }
        //Теперь проверка через шаблоны
        foreach ($this->patternMatches as $match) {
            if ($found_matches = RegExp::resolve($match, $text)) {
                if (is_array($found_matches)) $this->foundMatches = $found_matches;
                return true;
            }
        }
        return false;
    }
    public function getName()
    {
        return $this->name;
    }
}

