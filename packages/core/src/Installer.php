<?php

declare(strict_types=1);

namespace Tempest\Core;

use Attribute;

/**
 * Defines an installer that will be available as an option when using the `install` console command.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Installer
{
    public function __construct(
        public string $name,
        /** @var string|array<string> */
        public string|array $alias = [],
    ) {}
}
