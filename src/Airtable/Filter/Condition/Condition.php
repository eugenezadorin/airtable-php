<?php

namespace Zadorin\Airtable\Filter\Condition;

abstract class Condition
{
    public abstract function toString(): string;

    public function __toString()
	{
		return $this->toString();
	}
}
