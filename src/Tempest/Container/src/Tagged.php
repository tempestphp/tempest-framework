<?php

declare(strict_types=1);

namespace Tempest\Container;

use Attribute;

#[Attribute]
final readonly class Tagged
{
    public function __construct(public string $name)
    {
    }
}