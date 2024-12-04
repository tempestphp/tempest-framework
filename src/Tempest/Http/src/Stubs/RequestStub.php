<?php

declare(strict_types=1);

namespace Tempest\Http\Stubs;

use Tempest\Http\Request;
use Tempest\Validation\Rules\Length;

// TODO: Clean this up.
final class RequestStub extends Request
{
    #[Length(min: 10, max: 120)]
    public string $title;
}
