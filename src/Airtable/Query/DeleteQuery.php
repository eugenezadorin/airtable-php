<?php

declare(strict_types=1);

namespace Zadorin\Airtable\Query;

use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Record;
use Zadorin\Airtable\Recordset;

class DeleteQuery extends AbstractQuery
{
    /** @var Record[] */
    protected array $records = [];

    public function delete(Record ...$records): self
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
            $data['records'][] = $record->getId();
        }

        return Recordset::createFromResponse(
            $this->client->call('DELETE', '?' . http_build_query($data))
        );
    }
}
