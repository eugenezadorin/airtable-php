<?php

declare(strict_types=1);

namespace Zadorin\Airtable\Query;

use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Record;
use Zadorin\Airtable\Recordset;

class InsertQuery extends AbstractQuery
{
    /** @var Record[] */
    protected array $records = [];

    public function insert(Record ...$records): self
    {
        $this->records = $records;
        return $this;
    }

    public function execute(): Recordset
    {
        if (count($this->records) <= 0) {
            throw new Errors\RecordsNotSpecified('At least one record must be specified');
        }

        $data = ['records' => []];
        foreach ($this->records as $record) {
            $data['records'][] = [
                'fields' => $record->getFields()
            ];
        }
        return Recordset::createFromResponse(
            $this->client->call('POST', '', $data)
        );
    }
}
