<?php

namespace Zadorin\Airtable\Filter\Condition;

interface ConditionFactory
{
	/**
	 * @param string $field
	 * @param string $operator
	 * @param mixed $value
	 * @return Condition
	 */
	public function make(string $field, string $operator, $value): Condition;
}
