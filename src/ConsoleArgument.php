<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;
use Tempest\Support\ArrayHelper;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class ConsoleArgument
{
    /** @var string[] */
    public readonly array $help;

    /**
     * @param string|string[] $help
     * @param string[] $aliases
     */
    public function __construct(
        public readonly ?string $description = null,
        array|string $help = [],
        public readonly array $aliases = [],
    ) {
        $this->help = ArrayHelper::wrap($help);
    }
}
