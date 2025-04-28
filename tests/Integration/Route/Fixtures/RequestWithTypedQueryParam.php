<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Tempest\Router\IsRequest;
use Tempest\Router\Request;

final class RequestWithTypedQueryParam implements Request
{
    use IsRequest;

    public string $stringParam;

    public float $floatParam;

    public bool $boolParam;

    public int $intParam;
}
