<?php

namespace Zadorin\Airtable\Filter\Condition;

final class DateConditionFactory implements ConditionFactory
{
    private bool $useDateTime = false;

    public static function usingDateTime(): self
    {
        $factory = new self();
        $factory->useDateTime = true;

        return $factory;
    }

    public function make(string $field, string $operator, mixed $value): Condition
    {
        if (! ($value instanceof \DateTimeImmutable)) {
            $value = new \DateTimeImmutable((string) $value);
        }

        return $this->useDateTime
            ? new DateTimeCondition($field, $operator, $value)
            : new DateCondition($field, $operator, $value);
    }
}
