<?php

function client(): \Zadorin\Airtable\Client
{
    return new \Zadorin\Airtable\Client($_ENV['AIRTABLE_API_KEY'], $_ENV['AIRTABLE_TEST_DB']);
}