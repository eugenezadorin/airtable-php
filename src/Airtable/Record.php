<?php

declare(strict_types=1);

namespace Zadorin\Airtable;

use Zadorin\Airtable\Errors;
use DateTimeImmutable;

class Record
{
    protected ?string $id = null;

    protected array $fields = [];

    protected ?DateTimeImmutable $createdAt = null;

    protected bool $isDeleted = false;

    public function __construct(array $fields = [], ?string $id = null)
    {
        $this->id = $id;
        $this->fields = $fields;
        $this->createdAt = new DateTimeImmutable();
    }

    public static function createFromResponse(array $apiResponse): self
    {
        if (isset($apiResponse['id'])) {
            $id = (string)$apiResponse['id'];
        } else {
            throw new Errors\CannotCreateDto('id key is missing');
        }

        if (isset($apiResponse['fields']) && is_array($apiResponse['fields'])) {
            $fields = $apiResponse['fields'];
        } else {
            $fields = [];
        }

        if (isset($apiResponse['createdTime'])) {
            $createdAt = new DateTimeImmutable((string)$apiResponse['createdTime']);
        } else {
            $createdAt = null;
        }

        if (isset($apiResponse['deleted'])) {
            $isDeleted = (bool)$apiResponse['deleted'];
        } else {
            $isDeleted = false;
        }

        $record = new self($fields, $id);
        $record->createdAt = $createdAt;
        $record->isDeleted = $isDeleted;

        return $record;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }
}
