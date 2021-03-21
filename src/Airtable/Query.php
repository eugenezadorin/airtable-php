<?php

declare(strict_types=1);

namespace Zadorin\Airtable;

use Zadorin\Airtable\Errors;

class Query
{
    protected ?Client $client;

    public ?string $tableName = null;

    public array $selectFields = [];

    public array $filterConditions = [];

    public array $orderConditions = [];

    public int $limit = 100;

    public function __construct(?Client $client)
    {
        $this->client = $client;
    }

    public function execute(): Recordset
    {
        if (!$this->client) {
            throw new Errors\ClientNotSpecified('Airtable client must be specified');
        }
        return $this->client->execute($this);
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

    public function from(string $tableName): self
    {
        $this->tableName = $tableName;
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
