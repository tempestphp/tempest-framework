<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

class RequestWithEnum implements Request
{
    use IsRequest;

    public EnumForRequest $enumParam;
}
