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
    public function addExactMatch($phrase)
    {
        $this->exactMatches[] = $phrase;
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
    private function trigger($text)
    {
        $obj = new $this->controller();
        $obj->$this->method($text);
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
        foreach ($this->exactMatches as $phrase) {
            if ($text === $phrase) return $this->trigger($text);
        }
    }
    public function getName()
    {
        return $this->name;
    }
}

