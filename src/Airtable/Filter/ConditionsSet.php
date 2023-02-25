<?php

namespace Zadorin\Airtable\Filter;

use Zadorin\Airtable\ArgParser;
use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Filter\Condition\Condition;
use Zadorin\Airtable\Filter\Condition\ConditionFactory;

final class ConditionsSet implements \Stringable
{
    /** @var Condition[] */
    private array $conditions = [];

    /**
     * @param  array  $args One, two or three arguments
     *
     * @throws Errors\InvalidArgument
     */
    public function __construct(protected ConditionFactory $conditionFactory, protected array $args)
    {
        // @todo probably all this arg parsing and initialization is part of ConditionFactory.
        // @todo maybe i should make ConditionFactory abstract class instead of interface
        if (count($args) === 3) {
            $this->initAsFieldOperatorValue((string) $args[0], (string) $args[1], $args[2]);
        } elseif (count($args) === 2) {
            $this->initAsFieldEqualsValue((string) $args[0], $args[1]);
        } elseif (count($args) === 1 && is_array($args[0])) {
            $this->initFromArray($args[0]);
        } else {
            throw new Errors\InvalidArgument('Method where() expects one, two or three arguments');
        }
    }

    public function push(Condition $condition): self
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /** @return Condition[] */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    private function initAsFieldOperatorValue(string $field, string $operator, mixed $value): void
    {
        $condition = $this->conditionFactory->make($field, $operator, $value);
        $this->push($condition);
    }

    private function initAsFieldEqualsValue(string $field, mixed $value): void
    {
        $this->initAsFieldOperatorValue($field, '=', $value);
    }

    private function initFromArray(array $conditions): void
    {
        if (ArgParser::isArrayOfArrays($conditions)) {
            /** @var array $condition */
            foreach ($conditions as $condition) {
                if (count($condition) === 3) {
                    [$field, $operator, $value] = $condition;
                } elseif (count($condition) === 2) {
                    [$field, $value] = $condition;
                    $operator = '=';
                } else {
                    throw new Errors\InvalidArgument('Invalid where statement');
                }

                $condition = $this->conditionFactory->make((string) $field, (string) $operator, $value);
                $this->push($condition);
            }
        } else {
            /** @psalm-var mixed $value */
            foreach ($conditions as $field => $value) {
                $condition = $this->conditionFactory->make((string) $field, '=', $value);
                $this->push($condition);
            }
        }
    }

    public function __toString(): string
    {
        $formulas = [];
        foreach ($this->conditions as $condition) {
            $formulas[] = (string) $condition;
        }
        if (count($formulas) > 1) {
            return 'AND('.implode(', ', $formulas).')';
        }
        if (count($formulas) === 1) {
            return $formulas[0];
        }
        return '';
    }
}
