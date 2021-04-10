<?php

namespace Classes;

use Facades\Tlgr;
use Classes\Store;

class RulesProcessor
{
    private $text = '';
    private $rules = [];
    private $resolvedRules = [];
    
    public function setText($text)
    {
        $this->text = $text;
        return true;
    }
    public function addRule(Object $rule)
    {
        $this->rules[] = $rule;
    }
    public function run()
    {
        foreach ($this->rules as $rule) {
            if ($rule->resolve($this->text)) $this->resolvedRules[] = $rule;
        }
        $count = count($this->resolvedRules);
        if ($count === 0) {
            Tlgr::sendMessage('Не понял');
            return 0;
        } else if ($count === 1) {
            $this->resolvedRules[0]->trigger[$this->text];
            return 1;
        }
        if ($count > 1) {
            $resp = 'Запрос неоднозначен. Выберете вариант:' . PHP_EOL;
            foreach ($this->resolvedRules as $key => $rule) {
                $resp .= "#" . $key . " " . $rule->getName() . PHP_EOL;
            }
            Store::getInstance('bot')->setState(2);
            Store::getInstance('bot')->setPendingRules($this->resolvedRules);
            Tlgr::sendMessage($resp);
            return 2;
        }
    }
}