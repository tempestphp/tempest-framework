<?php

declare(strict_types=1);

namespace Tempest\Core;

use Exception;

use function Tempest\Support\arr;

final class EnvironmentValueWasInvalid extends Exception
{
    public function __construct(string $value)
    {
        $possibleValues = arr(Environment::cases())
            ->map(fn (Environment $environment) => $environment->value)
            ->join();

        parent::__construct("Invalid environment [{$value}]. Possible values are {$possibleValues}.");
    }
}
