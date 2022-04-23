<?php

namespace Zadorin\Airtable\Filter;

class LogicCollection
{
    public const OPERATOR_AND = 'AND';
    public const OPERATOR_OR = 'OR';
    public const OPERATOR_NOT = 'NOT';

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
     * @psalm-suppress UnnecessaryVarAnnotation
     */
    public function getFormula(): string
    {
        $infixExpression = [];
        $maxKey = count($this->conditionGroups) - 1;

        foreach (array_reverse($this->conditionGroups) as $k => [$operand, $operator]) {

            /** @var ConditionsSet $operand */
            /** @var string $operator */

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
                array_unshift($stack, sprintf('%s(%s, %s)', (string)$value, $arg1, $arg2));
            }
        }

        return array_shift($stack);
    }

    /**
     * @param ConditionsSet|string $arg
     */
    protected function isOperand($arg): bool
    {
        return $arg instanceof ConditionsSet;
    }

    /**
     * @param ConditionsSet|string $arg
     */
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
     * @psalm-param array<ConditionsSet|string> $infixExpression
     * @psalm-return array<ConditionsSet|string>
     */
    protected function infixToPostfix(array $infixExpression): array
    {
        /** @psalm-var array<ConditionsSet|string> */
        $result = [];

        /** @var string[] */
        $stack = [];

        foreach ($infixExpression as $symbol) {
            if ($symbol instanceof ConditionsSet) {
                $result[] = $symbol;
            } else {
                if (isset($stack[0]) && $this->getOperatorPriority($stack[0]) > $this->getOperatorPriority($symbol)) {
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
