<?php

namespace Tempest\Testing\Exceptions;

use Exception;
use Tempest\Testing\Test;

final class InvalidProviderData extends Exception
{
    public static function invalidMethodName(Test $test, string $name): self
    {
        return new self("No provider method named `{$name}` was found for `{$test->name}`");
    }

    public static function providerMethodMustReturnIterable(Test $test, string $name): self
    {
        return new self("The provider method `{$name}` must return an iterable for `{$test->name}`");
    }
}
