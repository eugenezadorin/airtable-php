<?php

declare(strict_types=1);

namespace Zadorin\Airtable;

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

    public function query(): Query
    {
        return new Query($this);
    }

    // @todo: replace params and return types with DTO
    /*public function insert(array $fields): array
    {
        return $this->call('POST', '', [
            'records' => [
                [
                    'fields' => $fields
                ]
            ]
        ]);
    }

    public function update(string $recordId, array $fields): array
    {
        return $this->call('PATCH', '', [
            'records' => [
                [
                    'id' => $recordId,
                    'fields' => $fields
                ]
            ]
        ]);
    }*/

    public function execute(Query $query): Recordset
    {
        if (!$query->tableName) {
            throw new Errors\TableNotSpecified('Table name must be specified');
        }

        $this->tableName = $query->tableName;

        $urlParams = [];

        if (count($query->selectFields) > 0) {
            $urlParams['fields'] = $query->selectFields;
        }

        if (count($query->filterConditions) > 0) {
            $formulas = [];
            // @todo: each condition should be object of Condition class
            foreach ($query->filterConditions as $field => $value) {
                $formulas[] = sprintf("{%s}='%s'", $field, $value);
            }

            $urlParams['filterByFormula'] = 'AND(' . implode(', ', $formulas) . ')';
        }

        if (count($query->orderConditions) > 0) {
            foreach ($query->orderConditions as $field => $direction) {
                $urlParams['sort'][] = ['field' => $field, 'direction' => $direction];
            }
        }

        if ($query->limit > 0) {
            $key = $query->limit <= 100 ? 'pageSize' : 'maxRecords';
            $urlParams[$key] = $query->limit;
        } else {
            $urlParams['pageSize'] = 0;
        }

        return $this->call('GET', '?' . http_build_query($urlParams));
    }

    // @todo: add json and curl as composer dependencies
    protected function call(string $method = 'GET', string $uri = '', array $data = [], array $headers = []): Recordset
    {
        $baseUri = sprintf('%s/%s/%s', self::BASE_URL, $this->databaseName, $this->tableName);
        $uri = $baseUri . $uri;
        $defaultHeaders = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
        $headers = array_merge($defaultHeaders, $headers);

        $handler = curl_init($uri);
        curl_setopt($handler, CURLOPT_HTTPHEADER, $this->curlHeaders($headers));
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);

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
