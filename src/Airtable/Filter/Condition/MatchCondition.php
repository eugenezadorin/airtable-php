<?php

namespace Zadorin\Airtable\Filter\Condition;

class MatchCondition extends Condition
{
	protected string $field;

	protected string $regexp;

	public function __construct(string $field, string $regexp)
	{
		$this->field = $field;
		$this->regexp = $regexp;
	}

	public function toString(): string
	{
		return sprintf("REGEX_MATCH({%s},'%s')", $this->field, $this->regexp);
	}
}
