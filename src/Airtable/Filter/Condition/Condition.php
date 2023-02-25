<?php

namespace Zadorin\Airtable\Filter\Condition;

abstract class Condition implements \Stringable
{
    abstract public function toString(): string;

    public function __toString(): string
    {
        return $this->toString();
    }
}
