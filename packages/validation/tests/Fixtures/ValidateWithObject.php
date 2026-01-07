<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Fixtures;

use Tempest\Validation\Rules\ValidateWith;

final class ValidateWithObject
{
    #[ValidateWith(static function (string $value): bool {
        return str_contains((string) $value, '@');
    })]
    public string $prop;
}
