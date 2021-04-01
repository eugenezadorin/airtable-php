<?php

namespace Zadorin\Airtable;

use Zadorin\Airtable\Errors;

class Request
{
    protected string $method = 'GET';

    protected string $uri;

    protected array $requestHeaders = [];

    protected string $requestBody;

    protected array $allowedMethods = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE'
    ];

    protected int $responseCode;

    protected string $responseBody;

    protected array $responseInfo = [];

    protected $handler;

    public function setMethod(string $method): self
    {
        $method = mb_strtoupper($method);
        if (!in_array($method, $this->allowedMethods)) {
            throw new Errors\MethodNotAllowed('Request method not allowed');
        }
        $this->method = $method;
        return $this;
    }

    public function setUri(string $uri): self
    {
        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new Errors\RequestError('Invalid URI');
        }
        $this->uri = $uri;
        return $this;
    }

    public function setHeaders(array $headers): self
    {
        $this->requestHeaders = $headers;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->requestBody = $body;
        return $this;
    }

    public function setData($fields): self
    {
        $this->requestBody = json_encode($fields, JSON_THROW_ON_ERROR);
        return $this;
    }

    public function getData()
    {
        return json_decode($this->responseBody, true, 512, JSON_THROW_ON_ERROR);
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    public function getPlainResponse(): string
    {
        return $this->responseBody;
    }

    public function getResponseInfo(): array
    {
        return $this->responseInfo;
    }

    public function isSuccess(): bool
    {
        if ($this->responseCode >= 200 && $this->responseCode < 300) {
            return true;
        }
        return false;
    }

    public function send()
    {
        $this->handler = curl_init($this->uri);
        curl_setopt($this->handler, CURLOPT_HTTPHEADER, $this->prepareCurlHeaders());
        curl_setopt($this->handler, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handler, CURLOPT_CUSTOMREQUEST, $this->method);

        if (!empty($this->requestBody)) {
            curl_setopt($this->handler, CURLOPT_POSTFIELDS, $this->requestBody);
        }

        $this->responseBody = curl_exec($this->handler);
        $this->responseInfo = curl_getinfo($this->handler);
        $this->responseCode = intval($this->responseInfo['http_code']);
        
        if ($this->responseBody === false) {
            $errorText = curl_error($this->handler);
            curl_close($this->handler);
            throw new Errors\RequestError($errorText, $this->responseCode);
        }

        curl_close($this->handler);
        return $this->responseBody;
    }

    protected function prepareCurlHeaders(): array
    {
        $result = [];
        foreach ($this->requestHeaders as $key => $value) {
            $result[] = "$key: $value";
        }
        return $result;
    }
}
