<?php

namespace Zadorin\Airtable\Filter\Condition;

class DateConditionFactory implements ConditionFactory
{
	protected bool $useDateTime = false;

	public static function usingDateTime(): self
	{
		$factory = new self();
		$factory->useDateTime = true;
		return $factory;
	}

	/**
	 * @param string $field
	 * @param string $operator
	 * @param mixed $value
	 * @return Condition
	 */
	public function make(string $field, string $operator, $value): Condition
	{
		if (!($value instanceof \DateTimeImmutable))
		{
			$value = new \DateTimeImmutable((string)$value);
		}

		return $this->useDateTime
			? new DateTimeCondition($field, $operator, $value)
			: new DateCondition($field, $operator, $value);
	}
}
