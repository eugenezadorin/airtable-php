<?php

namespace Zadorin\Airtable\Filter\Condition;

interface ConditionFactory
{
    public function make(string $field, string $operator, mixed $value): Condition;
}
