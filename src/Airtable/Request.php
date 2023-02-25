<?php

declare(strict_types=1);

namespace Zadorin\Airtable;

final class Request
{
    private string $method = 'GET';

    private string $uri = '';

    /**
     * @var array<string, string>
     */
    private array $requestHeaders = [];

    private string $requestBody = '';

    private const ALLOWED_METHODS = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE',
    ];

    private int $responseCode = 0;

    private string $responseBody = '';

    private array $responseInfo = [];

    public function setMethod(string $method): self
    {
        $method = mb_strtoupper($method);
        if (! in_array($method, self::ALLOWED_METHODS)) {
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

    public function setData(mixed $fields): self
    {
        $this->requestBody = json_encode($fields, JSON_THROW_ON_ERROR);

        return $this;
    }

    public function getResponseData(): mixed
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
        return ($this->responseCode >= 200 && $this->responseCode < 300);
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

        $this->responseCode = (int) $this->responseInfo['http_code'];

        if ($result === false) {
            $errorText = curl_error($handler);
            curl_close($handler);
            throw new Errors\RequestError($this, $errorText, $this->responseCode);
        }
        $this->responseBody = (string) $result;

        curl_close($handler);

        return $this->responseBody;
    }

    /**
     * @return string[]
     */
    private function prepareCurlHeaders(): array
    {
        $result = [];
        foreach ($this->requestHeaders as $key => $value) {
            $result[] = "$key: $value";
        }

        return $result;
    }
}
