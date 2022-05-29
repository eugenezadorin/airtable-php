<?php

declare(strict_types=1);

namespace Zadorin\Airtable\Query;

use Zadorin\Airtable\Client;
use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Filter\Condition\DateConditionFactory;
use Zadorin\Airtable\Filter\Condition\ScalarConditionFactory;
use Zadorin\Airtable\Filter\ConditionsSet;
use Zadorin\Airtable\Filter\LogicCollection;
use Zadorin\Airtable\Recordset;

class SelectQuery extends AbstractQuery
{
    public const MAX_PAGE_SIZE = 100;

    /** @var string[] */
    protected array $selectFields = [];

    protected ?LogicCollection $filterConditions = null;

	protected ?string $currentLogicOperator = null;

    protected ?string $rawFormula = null;

    /** @var array<string, string> */
    protected array $orderConditions = [];

    protected ?string $offset = null;

    protected bool $hasNextPage = true;

    protected int $limit = 100;

	protected ?string $view = null;

    public function execute(): Recordset
    {
        $urlParams = [];

        if (count($this->selectFields) > 0) {
            $urlParams['fields'] = $this->selectFields;
        }

		$formula = $this->getFormula();
		if (!empty($formula)) {
			$urlParams['filterByFormula'] = $formula;
		}

        if (count($this->orderConditions) > 0) {
            foreach ($this->orderConditions as $field => $direction) {
                $urlParams['sort'][] = ['field' => $field, 'direction' => $direction];
            }
        }

        $urlParams['pageSize'] = $this->limit > 0 ? $this->limit : 0;

        if ($this->offset !== null) {
            $urlParams['offset'] = $this->offset;
        }

		if ($this->view !== null) {
			$urlParams['view'] = $this->view;
		}

        return Recordset::createFromResponse(
            $this->client->call('GET', '?' . http_build_query($urlParams))
        );
    }

    public function nextPage(): ?Recordset
    {
        if ($this->hasNextPage) {
            $recordset = $this->execute();
            $this->offset = $recordset->getOffset();

            if ($this->offset === null || $recordset->count() === 0) {
                $this->hasNextPage = false;
            }

            if ($recordset->count() > 0) {
                return $recordset;
            }
        }

        return null;
    }

    public function select(string ...$fields): self
    {
        if (in_array('*', $fields)) {
            $this->selectFields = [];    
        } else {
            $this->selectFields = $fields;
        }
        return $this;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @throws Errors\InvalidArgument
     */
    public function where(): self
    {
        return $this->withFilterConditions(
			new ConditionsSet(new ScalarConditionFactory(), func_get_args())
		);
    }

	protected function withFilterConditions(ConditionsSet $conditions): self
	{
		if ($this->currentLogicOperator === LogicCollection::OPERATOR_OR) {
			$this->getFilterConditions()->or($conditions);
		} else {
			$this->getFilterConditions()->and($conditions);
		}
		$this->currentLogicOperator = null;
		return $this;
	}

	// @todo i think it's time to extract query builder into separate class
	public function __call(string $name, array $arguments)
	{
		$logic = LogicCollection::OPERATOR_AND;
		$baseMethod = $name;

		if (str_starts_with($name, 'and'))
		{
			$baseMethod = lcfirst(substr($name, 3));
		}
		elseif (str_starts_with($name, 'or'))
		{
			$logic = LogicCollection::OPERATOR_OR;
			$baseMethod = lcfirst(substr($name, 2));
		}

		if (method_exists($this, $baseMethod))
		{
			$this->currentLogicOperator = $logic;
			return $this->$baseMethod(...$arguments);
		}
		elseif (Client::hasMacro($baseMethod))
		{
			$this->currentLogicOperator = $logic;
			Client::callMacro($baseMethod, $arguments, $this);
			return $this;
		}

		throw new Errors\MethodNotExists("Method $name not found in query builder");
	}

	public function whereView(string $view): self
	{
		$this->view = $view;
		return $this;
	}

	/**
	 * @deprecated View is not actually part of the filter formula, so you can use AND-logic only.
	 */
	public function orWhereView(string $view): self
	{
		throw new Errors\LogicError('Cannot specify view using OR-operator');
	}

    public function whereRaw(string $formula): self
    {
        $this->rawFormula = $formula;
        return $this;
    }

	public function whereDate(): self
	{
		return $this->withFilterConditions(
			new ConditionsSet(new DateConditionFactory(), func_get_args())
		);
	}

	/**
	 * @param string $field
	 * @param string|\DateTimeImmutable $dateFrom
	 * @param string|\DateTimeImmutable $dateTo
	 * @return SelectQuery
	 */
	public function whereDateBetween(string $field, $dateFrom, $dateTo): self
	{
		return $this->whereDate([
			[$field, '>=', $dateFrom],
			[$field, '<=', $dateTo],
		]);
	}

	public function whereDateTime(): self
	{
		return $this->withFilterConditions(
			new ConditionsSet(DateConditionFactory::usingDateTime(), func_get_args())
		);
	}

	/**
	 * @param string $field
	 * @param string|\DateTimeImmutable $dateTimeFrom
	 * @param string|\DateTimeImmutable $dateTimeTo
	 * @return SelectQuery
	 */
	public function whereDateTimeBetween(string $field, $dateTimeFrom, $dateTimeTo): self
	{
		return $this->whereDateTime([
			[$field, '>=', $dateTimeFrom],
			[$field, '<=', $dateTimeTo],
		]);
	}

    public function getFormula(): string
    {
        if ($this->rawFormula !== null) {
            return $this->rawFormula;
        } elseif ($this->filterConditions !== null) {
            return $this->filterConditions->getFormula();
        }
        return '';
    }

    /**
     * @param array<string, string> $conditions 
     */
    public function orderBy(array $conditions): self
    {
        $this->orderConditions = $conditions;
        return $this;
    }

    public function limit(int $limit): self
    {
        if ($limit > self::MAX_PAGE_SIZE) {
            throw new Errors\PageSizeTooLarge(sprintf('Max pagesize is %s, given %s', self::MAX_PAGE_SIZE, $limit));
        }
        $this->limit = $limit;
        return $this;
    }

    public function paginate(int $pageCount): self
    {
        return $this->limit($pageCount);
    }

	protected function getFilterConditions(): LogicCollection
	{
		if ($this->filterConditions === null) {
			$this->filterConditions = new LogicCollection();
		}
		return $this->filterConditions;
	}
}
