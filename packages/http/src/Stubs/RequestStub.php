<?php

declare(strict_types=1);

namespace Tempest\Http\Stubs;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\Rules\HasLength;

final class RequestStub implements Request
{
    use IsRequest;

    #[HasLength(min: 10, max: 120)]
    public string $title;
}
