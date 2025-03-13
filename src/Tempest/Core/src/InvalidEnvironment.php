<?php

declare(strict_types=1);

namespace Tempest\Core;

use Exception;

use function Tempest\Support\arr;

final class InvalidEnvironment extends Exception
{
    public function __construct(string $value)
    {
        $possibleValues = arr(Environment::cases())->map(fn (Environment $environment) => $environment->value)->implode(', ');

        parent::__construct("Invalid environment value `{$value}`, possible values are {$possibleValues}.");
    }
}
