<?php

declare(strict_types=1);

namespace Zadorin\Airtable\Query;

use Zadorin\Airtable\Client;
use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Recordset;

abstract class AbstractQuery
{
    public function __construct(protected Client $client)
    {
        if ($this->client->getTable() === '') {
            throw new Errors\TableNotSpecified('Table name must be specified');
        }
    }

    abstract public function execute(): Recordset;
}
