<?php

namespace Zadorin\Airtable\Filter;

class LogicCollection
{
    const OPERATOR_AND = 'AND';
    const OPERATOR_OR = 'OR';
    const OPERATOR_NOT = 'NOT';

    /** @var array<int, array{0: ConditionsSet, 1: string}> */
    protected array $conditionGroups = [];

    public function and(ConditionsSet $conditions): self
    {
        $this->conditionGroups[] = [$conditions, self::OPERATOR_AND];
        return $this;
    }

    public function or(ConditionsSet $conditions): self
    {
        $this->conditionGroups[] = [$conditions, self::OPERATOR_OR];
        return $this;
    }

    /**
     * 
     */
    public function getFormula(): string
    {
        $infixExpression = [];
        $maxKey = count($this->conditionGroups) - 1;

        /** @var ConditionsSet $operand */
        /** @var string $operator */
        /** @var int $k */
        foreach (array_reverse($this->conditionGroups) as $k => [$operand, $operator]) {
            $infixExpression[] = $operand;
            if ($k < $maxKey) {
                $infixExpression[] = $operator;
            }
        }

        /** @link https://www.geeksforgeeks.org/evaluation-prefix-expressions/ */
        $postfixExpression = $this->infixToPostfix($infixExpression);
        $stack = [];
        foreach ($postfixExpression as $value) {
            if ($this->isOperand($value)) {
                array_unshift($stack, (string)$value);
            } else {
                $arg1 = array_shift($stack);
                $arg2 = array_shift($stack);
                array_unshift($stack, sprintf('%s(%s, %s)', $value, $arg1, $arg2));
            }
        }

        return array_shift($stack);
    }

    protected function isOperand($arg): bool
    {
        return $arg instanceof ConditionsSet;
    }

    protected function isOperator($arg): bool
    {
        return is_string($arg) && in_array($arg, [
            self::OPERATOR_AND,
            self::OPERATOR_NOT,
            self::OPERATOR_OR,
        ]);
    }

    /**
     * @link https://scanftree.com/Data_Structure/infix-to-prefix
     */
    protected function infixToPostfix(array $infixExpression): array
    {
        $result = [];
        $stack = [];

        foreach ($infixExpression as $symbol) {
            if ($symbol instanceof ConditionsSet) {
                $result[] = $symbol;
            } else {
                if (isset($stack[0]) && $this->getOperatorPriority((string)$stack[0]) > $this->getOperatorPriority((string)$symbol)) {
                    $result[] = array_shift($stack);
                }
                array_unshift($stack, $symbol);
            }
        }

        if (count($stack) > 0) {
            foreach ($stack as $operator) {
                $result[] = $operator;
            }
        }

        return $result;
    }

    protected function getOperatorPriority(string $operator): int
    {
        if ($operator === self::OPERATOR_NOT) {
            return 3;
        } elseif ($operator === self::OPERATOR_AND) {
            return 2;
        } else {
            return 1;
        }
    }
}
