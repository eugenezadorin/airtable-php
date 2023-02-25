<?php

namespace Zadorin\Airtable\Filter\Condition;

final class ArithmeticCondition extends Condition
{
    public function __construct(protected string $field, protected string $operator, protected mixed $value)
    {
    }

    public function toString(): string
    {
        $value = is_float($this->value) || is_int($this->value) ? $this->value : (string) $this->value;

        return sprintf("{%s}%s'%s'", $this->field, $this->operator, $value);
    }
}
