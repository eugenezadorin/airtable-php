<?php

namespace Zadorin\Airtable\Query;

use Zadorin\Airtable\Recordset;

class SelectQuery extends AbstractQuery
{
    protected array $selectFields = [];

    protected array $filterConditions = [];

    protected array $orderConditions = [];

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

        if ($this->limit > 0) {
            $key = $this->limit <= 100 ? 'pageSize' : 'maxRecords';
            $urlParams[$key] = $this->limit;
        } else {
            $urlParams['pageSize'] = 0;
        }

        return $this->client->call('GET', '?' . http_build_query($urlParams));
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
        $this->limit = $limit;
        return $this;
    }
}
