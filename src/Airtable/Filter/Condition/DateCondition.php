<?php

namespace Zadorin\Airtable\Filter\Condition;

use DateTimeImmutable;

final class DateCondition extends Condition
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
            ? $this->dateTime->modify('-1 day')
            : $this->dateTime;

        $dateFrom = $dateFrom->format('Y-m-d 23:59:59');

        return sprintf("IS_AFTER({%s}, '%s')", $this->field, $dateFrom);
    }

    private function upperBorderFormula(): string
    {
        $dateTo = $this->operator === '<='
            ? $this->dateTime->modify('+ 1 day')
            : $this->dateTime;

        $dateTo = $dateTo->format('Y-m-d 00:00:00');

        return sprintf("IS_BEFORE({%s}, '%s')", $this->field, $dateTo);
    }

    private function exactFormula(): string
    {
        $dayBefore = $this->dateTime->modify('-1 day');
        $dayAfter = $this->dateTime->modify('+1 day');

        $dateFrom = $dayBefore->format('Y-m-d 23:59:59');
        $dateTo = $dayAfter->format('Y-m-d 00:00:00');

        return sprintf("AND(IS_AFTER({%s}, '%s'), IS_BEFORE({%s}, '%s'))", $this->field, $dateFrom, $this->field, $dateTo);
    }
}
