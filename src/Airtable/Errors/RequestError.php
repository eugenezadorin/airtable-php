<?php

namespace Zadorin\Airtable\Errors;

use Zadorin\Airtable\Request;

class RequestError extends ApiError
{
    protected Request $lastRequest;

    /**
     * @param string $message
     * @param int $code
     * @param \Throwable $previous
     * @param Request $lastRequest
     */
    public function __construct($message = 'Request failure', $code = 0, \Throwable $previous = null, Request $lastRequest)
    {
        $this->lastRequest = $lastRequest;    
        parent::__construct($message, $code, $previous);
    }

    public function getLastRequest(): Request
    {
        return $this->lastRequest;
    }
}
