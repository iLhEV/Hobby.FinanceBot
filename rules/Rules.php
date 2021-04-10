<?php

namespace Rules;

use Classes\Rule;
use Classes\RulesProcessor;

class Rules
{
    public $rulesProcessor = null;

    public function __construct()
    {
        $this->rulesProcessor = new RulesProcessor();
    }
    public function create()
    {
        //Пример с точным совпадением
        $rule = new Rule('просмотр баланса');
        $rule->addExactMatches(['б', 'бал', 'баланс']);
        $rule->addResolution('BalanceController', 'get');
        $rule->example('баланс');
        $this->rulesProcessor->addRule($rule);

        //Пример совпадения по шаблону
        $rule = new Rule('установка значения баланса');
        $rule->addPatternMatch('б|бал|баланс {string} {amount}');
        $rule->addResolution('BalanceController', 'setVal');
        $rule->example('баланс альфа 100.00');
        $this->rulesProcessor->addRule($rule);
    }
    public function process($text)
    {
        $this->rulesProcessor->setText($text);
        $this->rulesProcessor->run();
    }
}