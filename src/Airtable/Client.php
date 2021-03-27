<?php

declare(strict_types=1);

namespace Zadorin\Airtable;

use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Query\SelectQuery;
use Zadorin\Airtable\Query\InsertQuery;
use Zadorin\Airtable\Query\UpdateQuery;
use Zadorin\Airtable\Query\DeleteQuery;

class Client
{
    public const BASE_URL = 'https://api.airtable.com/v0';

    protected string $apiKey = '';

    protected string $databaseName = '';

    protected string $tableName = '';

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
     * @var Record|array $args
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
     * @var Record $records
     * @return UpdateQuery
     */
    public function update(Record ...$records): UpdateQuery
    {
        $query = new UpdateQuery($this);
        $query->update(...$records);

        return $query;
    }

    /**
     * @var Record|string $args
     * @return DeleteQuery
     */
    public function delete(...$args): DeleteQuery
    {
        $records = ArgParser::makeRecordsFromIds(...$args);
        $query = new DeleteQuery($this);
        $query->delete(...$records);

        return $query;
    }

    public function call(string $method = 'GET', string $uri = '', array $data = [], array $headers = []): Recordset
    {
        $baseUri = sprintf('%s/%s/%s', self::BASE_URL, $this->databaseName, $this->tableName);
        $uri = $baseUri . $uri;
        $defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
        $headers = array_merge($defaultHeaders, $headers);
        $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        if (!in_array($method, $allowedMethods)) {
            throw new Errors\MethodNotAllowed('Request method not allowed');
        }

        $handler = curl_init($uri);
        curl_setopt($handler, CURLOPT_HTTPHEADER, $this->curlHeaders($headers));
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($data)) {
            curl_setopt($handler, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $result = curl_exec($handler);
        $httpCode = intval(curl_getinfo($handler, CURLINFO_HTTP_CODE));
        curl_close($handler);

        if ($httpCode !== 200) {
            throw new \Zadorin\Airtable\Errors\RequestError($result, $httpCode);
        }

        return Recordset::createFromResponse(
            json_decode($result, true, 512, JSON_THROW_ON_ERROR)
        );
    }

    private function curlHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $key => $value) {
            $result[] = "$key: $value";
        }
        return $result;
    }
}
