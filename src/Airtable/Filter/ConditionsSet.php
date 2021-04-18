<?php

namespace Zadorin\Airtable\Filter;

use Zadorin\Airtable\ArgParser;
use Zadorin\Airtable\Errors;

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
     * @param string $field
     * @param string $operator
     * @param string $value
     * @throws Errors\InvalidArgument
     */
    public static function buildFromArgs(array $args): self
    {
        $set = new self();

        if (count($args) === 3) {
            $set->push(new Condition((string)$args[0], (string)$args[1], $args[2]));
        } elseif (count($args) === 2) {
            $set->push(new Condition((string)$args[0], '=', $args[1]));
        } elseif (count($args) === 1) {

            if (ArgParser::isArrayOfArrays($args[0])) {
                foreach ($args[0] as $cond) {
                    if (count($cond) === 3) {
                        $set->push(new Condition((string)$cond[0], (string)$cond[1], $cond[2]));
                    } elseif (count($cond) === 2) {
                        $set->push(new Condition((string)$cond[0], '=', $cond[1]));
                    } else {
                        throw new Errors\InvalidArgument('Invalid where statement');
                    }
                }
            } elseif (is_array($args[0])) {
                foreach ($args[0] as $field => $value) {
                    $set->push(new Condition((string)$field, '=', $value));
                }
            } else {
                throw new Errors\InvalidArgument('Invalid where statement');
            }

        } else {
            throw new Errors\InvalidArgument('Method where() expects one, two or three arguments');
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
