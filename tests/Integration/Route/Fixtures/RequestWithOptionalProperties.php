<?php

namespace Tests\Tempest\Integration\Route\Fixtures;

use Tempest\Validation\Rules\IsNotEmptyString;
use Tempest\Validation\SkipIfMissing;

final class RequestWithOptionalProperties
{
    #[SkipIfMissing, IsNotEmptyString]
    public string $title;

    #[SkipIfMissing]
    public ?string $expiryDate;
}
