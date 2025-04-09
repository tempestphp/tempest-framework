<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Tempest\Router\IsRequest;
use Tempest\Router\Request;

class RequestWithEnum implements Request
{
    use IsRequest;

    public EnumForRequest $enumParam;
}
