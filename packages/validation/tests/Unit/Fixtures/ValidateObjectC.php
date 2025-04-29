<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Unit\Fixtures;

use Tempest\Validation\Rules\Email;
use Tempest\Validation\Rules\Length;

final class ValidateObjectC
{
    #[Length(min: 2)]
    public string $name;

    #[Email]
    public string $email;
}
