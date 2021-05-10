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
            if ($rule->resolve($this->text)) {
                $this->resolvedRules[] = $rule;
            }
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
            //Поэтому прежде чем признать правила неоднозначными выясню, есть ли среди них правило, у которого приоритет больше всех
            $rulesPrioritiesCount = [];
            $rulesPrioritiesKeys = [];
            $respBody = "";
            //Собираю в массив $rulesPriorities: ключ = приоритет, значение = кол-во правил с таким приоритетом
            foreach ($this->resolvedRules as $ruleKey => $rule) {
                // p("Имя правила: " . $rule->getName());
                // p("Приоритет правила: " . $rule->getPriority());
                if (!isset($rulesPrioritiesCount[$rule->getPriority()])) {
                    $rulesPrioritiesCount[$rule->getPriority()] = 1;
                    $rulesPrioritiesKeys[$rule->getPriority()] = [];
                } else {
                    $rulesPrioritiesCount[$rule->getPriority()]++;
                }
                $rulesPrioritiesKeys[$rule->getPriority()][] = $ruleKey;
            }
            $maxPriority = max(array_keys($rulesPrioritiesCount));
            //p("Приоритет выигравшего правила: " . $priorityWinnerKey);
            // exit;
            //Если количество правил-победителей с максимальным приоритетом ровно одному, то правило однозначно
            if ($rulesPrioritiesCount[$maxPriority] === 1) {
                //Срабатывает правило-победитель
                $this->resolvedRules[$rulesPrioritiesKeys[$maxPriority][0]]->trigger();
                return 3;
            } else {
                $resp = 'Запрос неоднозначен. Выберете вариант:' . PHP_EOL;
                foreach ($rulesPrioritiesKeys[$maxPriority] as $ruleKey) {
                    $rule = $this->resolvedRules[$rulesPrioritiesKeys[$maxPriority][$ruleKey]];
                    $resp .= "#" . $ruleKey . " " . $rule->getName() . PHP_EOL;
                }
                p($resp);
                return 2;
            }
        }
    }
}