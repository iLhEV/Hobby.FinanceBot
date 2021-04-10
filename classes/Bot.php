<?php

namespace Classes;

class Bot
{
    const STATES = [
        1 => 'жду вопрос',
        2 => 'жду уточнение'    
    ];
    private $state = 1;
    private $pendingRules = null;
    public function setState($state)
    {
        $this->state = $state;
    }
    public function pendingRules($rules)
    {
        $this->pendingRule = $rules;
    }
}