<?php

namespace Zadorin\Airtable\Filter;

class Condition
{
    protected string $field;

    protected string $operator;

    protected $value;

    public function __construct(string $field, string $operator, $value)
    {
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return sprintf("{%s}%s'%s'", $this->field, $this->operator, $this->value);
    }
}
