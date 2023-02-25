<?php

namespace Zadorin\Airtable\Errors;

use Zadorin\Airtable\Request;

final class RequestError extends ApiError
{
    public function __construct(
        protected Request $lastRequest,
        string $message = 'Request failure',
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getLastRequest(): Request
    {
        return $this->lastRequest;
    }
}
