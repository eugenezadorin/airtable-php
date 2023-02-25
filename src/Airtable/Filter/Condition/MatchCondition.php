<?php

namespace Zadorin\Airtable\Filter\Condition;

final class MatchCondition extends Condition
{
    public function __construct(protected string $field, protected string $regexp)
    {
    }

    public function toString(): string
    {
        return sprintf("REGEX_MATCH({%s},'%s')", $this->field, $this->regexp);
    }
}
