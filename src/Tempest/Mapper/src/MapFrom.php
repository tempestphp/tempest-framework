<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class MapFrom
{
    /** @var array<string> */
    public array $names;

    public function __construct(
        string ...$names,
    ) {
        $this->names = $names;
    }
}
