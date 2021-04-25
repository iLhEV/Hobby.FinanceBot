<?php

namespace Classes;

use Facades\Tlgr;
use Classes\Store;
use Classes\Rule;

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
    public function addRule(Rule $rule)
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
            $this->resolvedRules[0]->trigger();
            return 1;
        } else {
            //Неоднозначными могут быть правила только с одинаковым приоритетом...
            $respBody = "";
            $priorityWinnerKey = null;
            $previousKey = null;
            //Определение приоритета работает пока только для двух правил
            foreach ($this->resolvedRules as $key => $rule) {
                p($rule->getName());
                p($rule->getPriority());
                if($key > 0) {
                    $previousKey = $key - 1;
                    $previousRule = $this->resolvedRules[$previousKey];
                    if ($rule->getPriority() > $previousRule->getPriority()) {
                        $priorityWinnerKey = $key; 
                    } elseif ($rule->getPriority() < $previousRule->getPriority()) {
                        $priorityWinnerKey = $previousKey; 
                    } else {
                        $respBody .= "#" . $key . " " . $rule->getName() . PHP_EOL;
                    }
                } 
            }
            p("pwk: " . $priorityWinnerKey);
            if ($respBody !== ""){
                $resp = 'Запрос неоднозначен. Выберете вариант:' . PHP_EOL;
                //Store::getInstance('bot')->setState(2);
                //Store::getInstance('bot')->setPendingRules($this->resolvedRules);
                Tlgr::sendMessage($resp);
                return 2;
            } else {
                //Срабатывает правило-победитель
                //Здесь надо переписать будет, потому что дублирует тригер выше по коду, а может и не надо =)
                $this->resolvedRules[$priorityWinnerKey]->trigger();
                return 3;
            }
        }
    }
}