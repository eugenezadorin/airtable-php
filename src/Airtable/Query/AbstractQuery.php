<?php

namespace Zadorin\Airtable\Query;

use Zadorin\Airtable\Client;
use Zadorin\Airtable\Errors;

abstract class AbstractQuery
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
        if (!$this->client->getTable() === '') {
            throw new Errors\TableNotSpecified('Table name must be specified');
        }
    }

    public abstract function execute();
}
