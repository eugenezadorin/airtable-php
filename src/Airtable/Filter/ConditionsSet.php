<?php

namespace Zadorin\Airtable\Filter;

use Zadorin\Airtable\ArgParser;
use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Filter\Condition\Condition;
use Zadorin\Airtable\Filter\Condition\ConditionFactory;

class ConditionsSet
{
    /** @var Condition[] */
    protected array $conditions = [];

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

    /**
     * @param array $args One, two or three arguments
     * @throws Errors\InvalidArgument
     */
    public static function buildFromArgs(array $args): self
    {
        if (count($args) === 3) {
            return self::buildAsFieldOperatorValue((string)$args[0], (string)$args[1], $args[2]);
        } elseif (count($args) === 2) {
            return self::buildAsFieldEqualsValue((string)$args[0], $args[1]);
        } elseif (count($args) === 1 && is_array($args[0])) {
            return self::buildFromArray($args[0]);
        } else {
            throw new Errors\InvalidArgument('Method where() expects one, two or three arguments');
        }
    }

    /**
     * @param string $field
     * @param string $operator
     * @param mixed $value
     */
    protected static function buildAsFieldOperatorValue(string $field, string $operator, $value): self
    {
        $set = new self();
        $set->push(ConditionFactory::make($field, $operator, $value));
        return $set;
    }

    /**
     * @param string $field
     * @param mixed $value
     */
    protected static function buildAsFieldEqualsValue(string $field, $value): self
    {
        return self::buildAsFieldOperatorValue($field, '=', $value);
    }

    protected static function buildFromArray(array $conditions): self
    {
        $set = new self();

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

                $set->push(ConditionFactory::make((string)$field, (string)$operator, $value));
            }
            
        } else {

            /** @var mixed $value */
            foreach ($conditions as $field => $value) {
                $set->push(ConditionFactory::make((string)$field, '=', $value));
            }

        }

        return $set;
    }

    public function __toString()
    {
        $formulas = [];
        foreach ($this->conditions as $condition) {
            $formulas[] = (string)$condition;
        }
        if (count($formulas) > 1) {
            return 'AND(' . implode(', ', $formulas) . ')';
        } elseif (count($formulas) === 1) {
            return $formulas[0];
        } else {
            return '';
        }
    }
}
