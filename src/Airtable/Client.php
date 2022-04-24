<?php

declare(strict_types=1);

namespace Zadorin\Airtable;

use Stiphle\Throttle;
use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Query\SelectQuery;
use Zadorin\Airtable\Query\FindQuery;
use Zadorin\Airtable\Query\InsertQuery;
use Zadorin\Airtable\Query\UpdateQuery;
use Zadorin\Airtable\Query\DeleteQuery;

class Client
{
    public const BASE_URL = 'https://api.airtable.com/v0';

    public const MAX_RPS = 5;

    protected string $apiKey = '';

    protected string $databaseName = '';

    protected string $tableName = '';

    protected bool $throttling = true;

    protected ?Throttle\ThrottleInterface $throttler = null;

    protected ?Request $request = null;

	/**
	 * @var array<string, \Closure>
	 */
	protected static array $macros = [];

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

    /**
     * @param Record|string ...$args
     * @return FindQuery
     */
    public function find(...$args): FindQuery
    {
        $records = ArgParser::makeRecordsFromIds(...$args);
        $query = new FindQuery($this);
        $query->find(...$records);
        return $query;
    }

    /**
     * @param Record|array ...$args
     * @return InsertQuery
     */
    public function insert(...$args): InsertQuery
    {
        $records = ArgParser::makeRecordsFromFields(...$args);
        $query = new InsertQuery($this);
        $query->insert(...$records);

        return $query;
    }

    /**
     * @param Record ...$records
     * @return UpdateQuery
     */
    public function update(Record ...$records): UpdateQuery
    {
        $query = new UpdateQuery($this);
        $query->update(...$records);

        return $query;
    }

    /**
     * @param Record|string ...$args
     * @return DeleteQuery
     */
    public function delete(...$args): DeleteQuery
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
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array<string, string> $headers
     */
    public function call(string $method = 'GET', string $uri = '', array $data = [], array $headers = []): array
    {
        if ($this->throttling) {
            $this->throttle();
        }

        $baseUri = sprintf('%s/%s/%s', self::BASE_URL, $this->databaseName, $this->tableName);
        $uri = $baseUri . $uri;
        $defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
        $headers = array_merge($defaultHeaders, $headers);

        $this->request = (new Request())
            ->setUri($uri)
            ->setMethod($method)
            ->setHeaders($headers)
            ->setData($data);
            
        $this->request->send();

        if (!$this->request->isSuccess()) {
            throw new Errors\RequestError($this->request, 'Bad HTTP code', $this->request->getResponseCode());
        }

        $responseData = $this->request->getResponseData();
        if (!is_array($responseData)) {
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
		if (!self::hasMacro($key)) {
			throw new Errors\InvalidArgument("Macro '$key' not defined");
		}

		$closure = self::$macros[$key];
		$closure->call($context, ...$arguments);
	}

    protected function throttle(): void
    {
        if ($this->throttler === null) {
            $this->throttler = new Throttle\LeakyBucket;
        }
        $requests = self::MAX_RPS;
        $inOneSecond = 1000;
        $this->throttler->throttle('Zadorin\\Airtable', $requests, $inOneSecond);
    }
}
