<?php

declare(strict_types=1);

namespace Tempest\Router;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class WithRelations
{
    /** @var array<string> */
    public array $relations;

    public function __construct(string ...$relations)
    {
        $this->relations = $relations;
    }
}
