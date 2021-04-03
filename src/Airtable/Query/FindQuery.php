<?php

declare(strict_types=1);

namespace Zadorin\Airtable\Query;

use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Record;
use Zadorin\Airtable\Recordset;

class FindQuery extends AbstractQuery
{
    /** @var Record[] */
    protected array $records = [];

    public function find(Record ...$records): self
    {
        $this->records = $records;
        return $this;
    }

    public function execute(): Recordset
    {
        if (count($this->records) <= 0) {
            throw new Errors\RecordsNotSpecified('At least one record must be specified');
        }

        foreach ($this->records as $record) {
            $recordId = $record->getId();
            if (mb_strlen((string)$recordId) <= 0) {
                throw new Errors\RecordsNotSpecified('Record id must be specified');
            }
        }

        $records = [];
        foreach ($this->records as $record) {
            $response = $this->client->call('GET', '/' . (string)$record->getId());
            $records[] = Record::createFromResponse($response);
        }

        return new Recordset($records);
    }
}
