<?php

namespace Classes;

class Rule
{
    private $name = '';
    private $exactMatches = [];
    private $controller ='';
    private $method = '';

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

    }
    public function addResolution($controller, $method)
    {
        $this->controller = "Controllers\\" . $controller;
        $this->method = $method;
        return true;
    }
    public function trigger($text)
    {
        $obj = new $this->controller();
        $obj->{$this->method}($text);
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
        return false;
    }
    public function getName()
    {
        return $this->name;
    }
}

