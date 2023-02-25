<?php

namespace Zadorin\Airtable\Filter\Condition;

use DateTimeImmutable;

final class DateTimeCondition extends Condition
{
    public function __construct(protected string $field, protected string $operator, protected DateTimeImmutable $dateTime)
    {
    }

    public function toString(): string
    {
        return match ($this->operator) {
            '>', '>=' => $this->lowerBorderFormula(),
            '<', '<=' => $this->upperBorderFormula(),
            default => $this->exactFormula(),
        };
    }

    private function lowerBorderFormula(): string
    {
        $dateFrom = $this->operator === '>='
            ? $this->dateTime->modify('-1 second')
            : $this->dateTime;

        $dateFrom = $dateFrom->format(DateTimeImmutable::ISO8601);

        return sprintf("IS_AFTER({%s}, '%s')", $this->field, $dateFrom);
    }

    private function upperBorderFormula(): string
    {
        $dateTo = $this->operator === '<='
            ? $this->dateTime->modify('+1 second')
            : $this->dateTime;

        $dateTo = $dateTo->format(DateTimeImmutable::ISO8601);

        return sprintf("IS_BEFORE({%s}, '%s')", $this->field, $dateTo);
    }

    private function exactFormula(): string
    {
        $dateTime = $this->dateTime->format(DateTimeImmutable::ISO8601);

        return sprintf("IS_SAME({%s}, '%s')", $this->field, $dateTime);
    }
}
