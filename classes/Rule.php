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
    public $foundMatches = [];
    public $text = '';
    public $dateFilter = false;

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
        $obj->{$this->method}($this);
        return true;
    }
    //Present example of text in call
    public function example($text)
    {
        return true;
    }
    public function resolve($text)
    {
        //Apply date filter if it's activated
        if ($this->isDateFilterActive()) {
            $this->dateFilter = new DateFilter($text);
            $text = trim($this->dateFilter->getProcessedText());
        } else {
            $text = trim($text);
        }
        
        $this->text = $text;

        //First check for exact matches
        foreach ($this->exactMatches as $match) {
            if ($text === $match) return true;
        }
        //Now check for patterns
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
    public function activateDateFilter()
    {
        $this->dateFilter = true;
    }
    public function isDateFilterActive()
    {
        return boolval($this->dateFilter);
    }
    public function getText()
    {
        return $this->text;
    }
}

