<?php

namespace Zadorin\Airtable\Filter\Condition;

use \DateTimeImmutable;

class DateTimeCondition extends Condition
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
			? $this->dateTime->modify('-1 second')
			: $this->dateTime;

		$dateFrom = $dateFrom->format(DateTimeImmutable::ISO8601);

		return sprintf("IS_AFTER({%s}, '%s')", $this->field, $dateFrom);
	}

	protected function upperBorderFormula(): string
	{
		$dateTo = $this->operator === '<='
			? $this->dateTime->modify('+1 second')
			: $this->dateTime;

		$dateTo = $dateTo->format(DateTimeImmutable::ISO8601);

		return sprintf("IS_BEFORE({%s}, '%s')", $this->field, $dateTo);
	}

	protected function exactFormula(): string
	{
		$dateTime = $this->dateTime->format(DateTimeImmutable::ISO8601);
		return sprintf("IS_SAME({%s}, '%s')", $this->field, $dateTime);
	}
}
