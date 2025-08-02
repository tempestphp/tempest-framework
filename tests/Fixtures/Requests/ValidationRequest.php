<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Requests;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\Rules\IsBetween;

final class ValidationRequest implements Request
{
    use IsRequest;

    #[IsBetween(min: 1, max: 10)]
    public int $number;
}
