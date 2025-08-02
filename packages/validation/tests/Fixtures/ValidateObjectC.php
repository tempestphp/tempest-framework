<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Fixtures;

use Tempest\Validation\Rules\HasLength;
use Tempest\Validation\Rules\IsEmail;

final class ValidateObjectC
{
    #[HasLength(min: 2)]
    public string $name;

    #[IsEmail]
    public string $email;
}
