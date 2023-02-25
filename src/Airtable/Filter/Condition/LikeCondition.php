<?php

namespace Zadorin\Airtable\Filter\Condition;

final class LikeCondition extends Condition
{
    public function __construct(protected string $field, protected string $pattern)
    {
    }

    public function toString(): string
    {
        return sprintf("REGEX_MATCH({%s},'%s')", $this->field, $this->buildRegexp());
    }

    private function buildRegexp(): string
    {
        $innerRegexp = str_replace($this->getWildcardSymbol(), '(.*)', $this->pattern);

        return $this->hasWildcard() ? "^$innerRegexp$" : $innerRegexp;
    }

    private function hasWildcard(): bool
    {
        return mb_strpos($this->pattern, $this->getWildcardSymbol()) !== false;
    }

    private function getWildcardSymbol(): string
    {
        return '%';
    }
}
