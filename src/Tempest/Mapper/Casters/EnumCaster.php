<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;

final readonly class EnumCaster implements Caster
{
    public function __construct(private string $enum)
    {
    }

    public function cast(mixed $input): object
    {
        if ($input instanceof $this->enum) {
            return $input;
        }

        return forward_static_call("{$this->enum}::from", $input);
    }
}
