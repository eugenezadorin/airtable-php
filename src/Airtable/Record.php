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

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
        $this->createdAt = new DateTimeImmutable();
    }

    public static function createFromResponse(array $apiResponse): self
    {
        if (!isset($apiResponse['id'])) {
            throw new Errors\CannotCreateDto('id key is missing');
        }

        $record = new self($apiResponse['fields'] ?? []);
        $record->id = $apiResponse['id'];
        $record->createdAt = isset($apiResponse['createdTime']) ? new DateTimeImmutable($apiResponse['createdTime']) : null;
        $record->isDeleted = $apiResponse['deleted'] ?? false;

        return $record;
    }

    public function getId(): ?string
    {
        return $this->id;
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
