<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Fixtures;

use Tempest\Validation\Rules\Predicate;

final class ObjectWithPredicateValidation
{
    #[Predicate(static function (string $value): bool {
        return str_contains((string) $value, '@');
    })]
    public string $prop;
}
