<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Requests;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Http\Sensitive;
use Tempest\Validation\Rules\IsNotEmptyString;

final class SensitiveFieldRequest implements Request
{
    use IsRequest;

    #[IsNotEmptyString]
    public string $not_sensitive_param;

    #[Sensitive]
    #[IsNotEmptyString]
    public string $sensitive_param;
}
