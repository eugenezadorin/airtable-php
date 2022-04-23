<?php

namespace Zadorin\Airtable\Filter\Condition;

use \DateTimeImmutable;

class DateCondition extends Condition
{
	protected string $field;

	protected DateTimeImmutable $dateTime;

	protected string $operator;

	public function __construct(string $field, string $operator, DateTimeImmutable $dateTime)
	{
		$this->field = $field;
		$this->dateTime = $dateTime;
		$this->operator = $operator;
	}

	public function toString(): string
	{
		switch ($this->operator)
		{
			case '>':
			case '>=':
				return $this->lowerBorderFormula();

			case '<':
			case '<=':
				return $this->upperBorderFormula();

			default:
				return $this->exactFormula();
		}
	}

	protected function lowerBorderFormula(): string
	{
		$dateFrom = $this->operator === '>='
			? $this->dateTime->modify('-1 day')
			: $this->dateTime;

		$dateFrom = $dateFrom->format('Y-m-d 23:59:59');

		return sprintf("IS_AFTER({%s}, '%s')", $this->field, $dateFrom);
	}

	protected function upperBorderFormula(): string
	{
		$dateTo = $this->operator === '<='
			? $this->dateTime->modify('+ 1 day')
			: $this->dateTime;

		$dateTo = $dateTo->format('Y-m-d 00:00:00');

		return sprintf("IS_BEFORE({%s}, '%s')", $this->field, $dateTo);
	}

	protected function exactFormula(): string
	{
		$dayBefore = $this->dateTime->modify('-1 day');
		$dayAfter = $this->dateTime->modify('+1 day');

		$dateFrom = $dayBefore->format('Y-m-d 23:59:59');
		$dateTo = $dayAfter->format('Y-m-d 00:00:00');

		return sprintf("AND(IS_AFTER({%s}, '%s'), IS_BEFORE({%s}, '%s'))", $this->field, $dateFrom, $this->field, $dateTo);
	}
}
