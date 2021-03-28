<?php

declare(strict_types=1);

namespace Zadorin\Airtable\Query;

use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Recordset;

class SelectQuery extends AbstractQuery
{
    public const MAX_PAGE_SIZE = 100;

    protected array $selectFields = [];

    protected array $filterConditions = [];

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

        if (count($this->filterConditions) > 0) {
            $formulas = [];
            // @todo: each condition should be object of Condition class
            foreach ($this->filterConditions as $field => $value) {
                $formulas[] = sprintf("{%s}='%s'", $field, $value);
            }

            $urlParams['filterByFormula'] = 'AND(' . implode(', ', $formulas) . ')';
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

        return $this->client->call('GET', '?' . http_build_query($urlParams));
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

    public function where(array $conditions): self
    {
        $this->filterConditions = $conditions;
        return $this;
    }

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
