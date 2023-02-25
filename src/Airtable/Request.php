<?php

namespace Zadorin\Airtable;

class Request
{
    protected string $method = 'GET';

    protected string $uri = '';

    /**
     * @var array<string, string>
     */
    protected array $requestHeaders = [];

    protected string $requestBody = '';

    protected array $allowedMethods = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE',
    ];

    protected int $responseCode = 0;

    protected string $responseBody = '';

    protected array $responseInfo = [];

    public function setMethod(string $method): self
    {
        $method = mb_strtoupper($method);
        if (! in_array($method, $this->allowedMethods)) {
            throw new Errors\MethodNotAllowed('Request method not allowed');
        }
        $this->method = $method;

        return $this;
    }

    public function setUri(string $uri): self
    {
        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new Errors\InvalidArgument('Invalid URI');
        }
        $this->uri = $uri;

        return $this;
    }

    /**
     * @param  array<string, string>  $headers
     */
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

    /**
     * @param  mixed  $fields
     */
    public function setData($fields): self
    {
        $this->requestBody = json_encode($fields, JSON_THROW_ON_ERROR);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponseData()
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

    public function send(): string
    {
        $handler = curl_init($this->uri);
        curl_setopt($handler, CURLOPT_HTTPHEADER, $this->prepareCurlHeaders());
        curl_setopt($handler, CURLINFO_HEADER_OUT, true);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, $this->method);

        if (! empty($this->requestBody)) {
            curl_setopt($handler, CURLOPT_POSTFIELDS, $this->requestBody);
        }

        $result = curl_exec($handler);

        $this->responseInfo = (array) curl_getinfo($handler);

        $this->responseCode = intval($this->responseInfo['http_code']);

        if ($result === false) {
            $errorText = curl_error($handler);
            curl_close($handler);
            throw new Errors\RequestError($this, $errorText, $this->responseCode);
        } else {
            $this->responseBody = (string) $result;
        }

        curl_close($handler);

        return $this->responseBody;
    }

    /**
     * @return string[]
     */
    protected function prepareCurlHeaders(): array
    {
        $result = [];
        foreach ($this->requestHeaders as $key => $value) {
            $result[] = "$key: $value";
        }

        return $result;
    }
}
