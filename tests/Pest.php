<?php

use Dew\Tablestore\Tablestore;

function integrationTestEnabled(): bool
{
    $value = getenv('INTEGRATION_TEST_ENABLED');

    return filter_var($value, FILTER_VALIDATE_BOOL);
}

function tablestore(): Tablestore
{
    return new Tablestore(
        getenv('ACS_ACCESS_KEY_ID'), getenv('ACS_ACCESS_KEY_SECRET'),
        getenv('TS_ENDPOINT'), getenv('TS_INSTNACE')
    );
}
