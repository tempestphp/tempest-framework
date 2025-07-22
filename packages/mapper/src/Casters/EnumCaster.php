<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Mapper\Caster;
use UnitEnum;

final readonly class EnumCaster implements Caster
{
    /**
     * @param class-string<UnitEnum> $enum
     */
    public function __construct(
        private string $enum,
    ) {}

    public function cast(mixed $input): ?object
    {
        if ($input === null) {
            return null;
        }

        if (is_a($input, $this->enum)) {
            return $input;
        }

        if (defined("{$this->enum}::{$input}")) {
            return constant("{$this->enum}::{$input}");
        }

        return forward_static_call("{$this->enum}::from", $input);
    }
}
