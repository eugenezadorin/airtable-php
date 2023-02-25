<?php

namespace Zadorin\Airtable\Filter\Condition;

abstract class Condition
{
    abstract public function toString(): string;

    public function __toString()
    {
        return $this->toString();
    }
}
