<?php

declare(strict_types=1);

namespace Zadorin\Airtable\Query;

use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Filter\ConditionsSet;
use Zadorin\Airtable\Filter\LogicCollection;
use Zadorin\Airtable\Recordset;

class SelectQuery extends AbstractQuery
{
    public const MAX_PAGE_SIZE = 100;

    /** @var string[] */
    protected array $selectFields = [];

    protected ?LogicCollection $filterConditions = null;

    protected ?string $rawFormula = null;

    /** @var array<string, string> */
    protected array $orderConditions = [];

    protected ?string $offset = null;

    protected bool $hasNextPage = true;

    protected int $limit = 100;

    public function execute(): Recordset
    {
        $urlParams = [];

        if (count($this->selectFields) > 0) {
            $urlParams['fields'] = $this->selectFields;
        }

        if ($this->rawFormula !== null) {
            $urlParams['filterByFormula'] = $this->rawFormula;
        } elseif ($this->filterConditions !== null) {
            $urlParams['filterByFormula'] = $this->filterConditions->getFormula();
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
        $this->filterConditions = new LogicCollection();
        $conditions = ConditionsSet::buildFromArgs(func_get_args());
        $this->filterConditions->and($conditions);
        return $this;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @throws Errors\InvalidArgument
     * @see SelectQuery::where()
     */
    public function andWhere(): self
    {
        if ($this->filterConditions === null) {
            $this->filterConditions = new LogicCollection();
        }
        $conditions = ConditionsSet::buildFromArgs(func_get_args());
        $this->filterConditions->and($conditions);
        return $this;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @throws Errors\InvalidArgument
     * @see SelectQuery::where()
     */
    public function orWhere(): self
    {
        if ($this->filterConditions === null) {
            $this->filterConditions = new LogicCollection();
        }
        $conditions = ConditionsSet::buildFromArgs(func_get_args());
        $this->filterConditions->or($conditions);
        return $this;
    }

    public function whereRaw(string $formula): self
    {
        $this->rawFormula = $formula;
        return $this;
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
}
