<?php

declare(strict_types=1);

namespace Zadorin\Airtable\Query;

use Zadorin\Airtable\Client;
use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Recordset;

abstract class AbstractQuery
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
        if ($this->client->getTable() === '') {
            throw new Errors\TableNotSpecified('Table name must be specified');
        }
    }

    /**
     * @return Recordset
     */
    public abstract function execute(): Recordset;
}
