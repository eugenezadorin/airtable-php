<?php

namespace Zadorin\Airtable\Filter\Condition;

final class ScalarConditionFactory implements ConditionFactory
{
    public function make(string $field, string $operator, mixed $value): Condition
    {
        return match ($operator) {
            'like' => new LikeCondition($field, (string) $value),
            'match' => new MatchCondition($field, (string) $value),
            default => new ArithmeticCondition($field, $operator, $value),
        };
    }
}
