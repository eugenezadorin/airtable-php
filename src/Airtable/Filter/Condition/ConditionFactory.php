<?php

namespace Zadorin\Airtable\Filter\Condition;

interface ConditionFactory
{
    /**
     * @param  mixed  $value
     */
    public function make(string $field, string $operator, $value): Condition;
}
