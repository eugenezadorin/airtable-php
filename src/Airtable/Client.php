<?php

declare(strict_types=1);

namespace Zadorin\Airtable;

use Stiphle\Throttle;
use Zadorin\Airtable\Query\DeleteQuery;
use Zadorin\Airtable\Query\FindQuery;
use Zadorin\Airtable\Query\InsertQuery;
use Zadorin\Airtable\Query\SelectQuery;
use Zadorin\Airtable\Query\UpdateQuery;

final class Client
{
    private const BASE_URL = 'https://api.airtable.com/v0';

    private const MAX_RPS = 5;

    private string $apiKey = '';

    private string $databaseName = '';

    private string $tableName = '';

    private bool $throttling = true;

    private ?Throttle\ThrottleInterface $throttler = null;

    private ?Request $request = null;

    /**
     * @var array<string, \Closure>
     */
    private static array $macros = [];

    public function __construct(string $apiKey, string $databaseName)
    {
        $this->apiKey = $apiKey;
        $this->databaseName = $databaseName;
    }

    public function table(string $tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function getTable(): string
    {
        return $this->tableName;
    }

    public function select(string ...$fields): SelectQuery
    {
        $query = new SelectQuery($this);
        $query->select(...$fields);

        return $query;
    }

    public function find(Record|string ...$args): FindQuery
    {
        $records = ArgParser::makeRecordsFromIds(...$args);
        $query = new FindQuery($this);
        $query->find(...$records);

        return $query;
    }

    public function insert(Record|array ...$args): InsertQuery
    {
        $records = ArgParser::makeRecordsFromFields(...$args);
        $query = new InsertQuery($this);
        $query->insert(...$records);

        return $query;
    }

    public function update(Record ...$records): UpdateQuery
    {
        $query = new UpdateQuery($this);
        $query->update(...$records);

        return $query;
    }

    public function delete(Record|string ...$args): DeleteQuery
    {
        $records = ArgParser::makeRecordsFromIds(...$args);
        $query = new DeleteQuery($this);
        $query->delete(...$records);

        return $query;
    }

    public function throttling(?bool $throttling = null): bool
    {
        if ($throttling !== null) {
            $this->throttling = $throttling;
        }

        return $this->throttling;
    }

    public function getLastRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * @param  array<string, string>  $headers
     */
    public function call(string $method = 'GET', string $uri = '', array $data = [], array $headers = []): array
    {
        if ($this->throttling) {
            $this->throttle();
        }

        $baseUri = sprintf('%s/%s/%s', self::BASE_URL, $this->databaseName, $this->tableName);
        $uri = $baseUri.$uri;
        $defaultHeaders = [
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ];
        $headers = array_merge($defaultHeaders, $headers);

        $this->request = (new Request())
            ->setUri($uri)
            ->setMethod($method)
            ->setHeaders($headers)
            ->setData($data);

        $this->request->send();

        if (! $this->request->isSuccess()) {
            throw new Errors\RequestError($this->request, 'Bad HTTP code', $this->request->getResponseCode());
        }

        $responseData = $this->request->getResponseData();
        if (! is_array($responseData)) {
            throw new Errors\RequestError($this->request, 'Empty body', $this->request->getResponseCode());
        }

        return $responseData;
    }

    public static function macro(string $key, \Closure $callback): void
    {
        $key = trim($key);
        if (empty($key)) {
            throw new Errors\InvalidArgument('Macro key must be non-empty string');
        }
        if (str_starts_with($key, 'and') || str_starts_with($key, 'or')) {
            throw new Errors\InvalidArgument('Macro key cannot start with or-/and- prefix');
        }
        self::$macros[$key] = $callback;
    }

    public static function hasMacro(string $key): bool
    {
        return isset(self::$macros[$key]);
    }

    public static function callMacro(string $key, array $arguments, object $context): void
    {
        if (! self::hasMacro($key)) {
            throw new Errors\InvalidArgument("Macro '$key' not defined");
        }

        $closure = self::$macros[$key];
        $closure->call($context, ...$arguments);
    }

    private function throttle(): void
    {
        if ($this->throttler === null) {
            $this->throttler = new Throttle\LeakyBucket();
        }
        $requests = self::MAX_RPS;
        $inOneSecond = 1000;
        $this->throttler->throttle('Zadorin\\Airtable', $requests, $inOneSecond);
    }
}
