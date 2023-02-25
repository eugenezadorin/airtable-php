<?php

namespace Zadorin\Airtable\Filter\Condition;

class ScalarConditionFactory implements ConditionFactory
{
    /**
     * @param  mixed  $value
     */
    public function make(string $field, string $operator, $value): Condition
    {
        switch ($operator) {
            case 'like':
                return new LikeCondition($field, (string) $value);

            case 'match':
                return new MatchCondition($field, (string) $value);

            default:
                return new ArithmeticCondition($field, $operator, $value);
        }
    }
}
