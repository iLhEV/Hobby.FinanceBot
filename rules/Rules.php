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
        $rule = new Rule('просмотр баланса');
        $rule->addExactMatches(['б', 'бал', 'баланс']);
        $rule->addResolution('BalanceController', 'get');
        // $rule->example('баланс');
        $this->rulesProcessor->addRule($rule);

        $rule = new Rule('установка значения баланса');
        $rule->addPatternMatch('б|бал|баланс {word} {amount}');
        $rule->addResolution('BalanceController', 'setVal');
        // $rule->example('баланс альфа 100.00');
        $this->rulesProcessor->addRule($rule);

        $rule = new Rule('фиксировать баланс');
        $rule->addExactMatches(['баланс фиксировать', 'фиксбал']);
        $rule->addResolution('BalanceFixationController', 'make');
        $this->rulesProcessor->addRule($rule);

        $rule = new Rule('список фиксаций баланса');
        $rule->addExactMatches(['фикс', 'фиксации']);
        $rule->addResolution('BalanceFixationController', 'getAll');
        $this->rulesProcessor->addRule($rule);

        $rule = new Rule('список доходов');
        $rule->addExactMatches(['доход', 'дох', 'дх']);
        $rule->addResolution('IncomeController', 'get');
        $this->rulesProcessor->addRule($rule);

        $rule = new Rule('добавление дохода');
        $rule->addPatternMatch('доход|дох|дх {word} {amount}');
        $rule->addResolution('IncomeController', 'add');
        $this->rulesProcessor->addRule($rule);

        $rule = new Rule('добавление траты');
        $rule->addPatternMatch('{string} {amount}');
        $rule->addResolution('SpendingController', 'add');
        $this->rulesProcessor->addRule($rule);
    }
    public function process($text)
    {
        $this->rulesProcessor->setText($text);
        $this->rulesProcessor->run();
    }
}