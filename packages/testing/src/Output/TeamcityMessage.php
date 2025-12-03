<?php

namespace Tempest\Testing\Output;

use function Tempest\Support\arr;

final class TeamcityMessage
{
    public function __construct(
        private TeamcityMessageName $name,
        /** array<string, string> */
        private array $parameters = [],
    ) {}

    public function __toString(): string
    {
        return sprintf(
            '##teamcity[%s %s]',
            $this->name->value,
            arr($this->parameters)->map(fn (string $value, string $key) => "{$key}='{$value}'")->implode(' '),
        );
    }
}
