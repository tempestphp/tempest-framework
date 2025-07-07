<?php

namespace Tests\Tempest\Integration\Route;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class RequestForInvalidMap implements Request
{
    use IsRequest;
}
