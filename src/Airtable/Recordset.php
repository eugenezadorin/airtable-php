<?php

declare(strict_types=1);

namespace Zadorin\Airtable;

class Recordset
{
    protected array $records = [];

    public static function createFromResponse(array $apiResponse): self
    {
        if (!isset($apiResponse['records'])) {
            throw new Errors\CannotCreateDto('records key is missing');
        }

        $recordset = new self();

        if (is_array($apiResponse['records'])) {
            foreach ($apiResponse['records'] as $apiRecord) {
                $recordset->records[] = Record::createFromResponse($apiRecord);
            }
        }
        
        return $recordset;
    }

    public function fetchAll(): array
    {
        return $this->records;
    }

    public function fetch(): ?Record
    {
        $key = key($this->records);
        if ($key === null) {
            return null;
        }
        $value = current($this->records);
        next($this->records);
        return $value;
    }

    public function count(): int
    {
        return count($this->records);
    }

    public function isEmpty(): bool
    {
        return count($this->records) === 0;
    }

    public function asArray(): array
    {
        $result = [];
        foreach ($this->records as $record) {
            $result[] = $record->getFields();
        }
        return $result;
    }
}
