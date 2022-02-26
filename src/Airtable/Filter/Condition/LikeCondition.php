<?php

namespace Zadorin\Airtable\Filter\Condition;

class LikeCondition extends Condition
{
	protected string $field;

	protected string $pattern;

	public function __construct(string $field, string $pattern)
	{
		$this->field = $field;
		$this->pattern = $pattern;
	}

	public function toString(): string
	{
		return sprintf("REGEX_MATCH({%s},'%s')", $this->field, $this->buildRegexp());
	}

	protected function buildRegexp(): string
	{
		$innerRegexp = str_replace($this->getWildcardSymbol(), '(.*)', $this->pattern);

		return $this->hasWildcard() ? "^$innerRegexp$" : $innerRegexp;
	}

	protected function hasWildcard(): bool
	{
		return mb_strpos($this->pattern, $this->getWildcardSymbol()) !== false;
	}

	protected function getWildcardSymbol(): string
	{
		return '%';
	}
}
