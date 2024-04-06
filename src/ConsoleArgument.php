<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;
use Tempest\Support\ArrayHelper;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class ConsoleArgument
{
    /** @var string[] */
    private array $help;

    /**
     * @param string|string[] $help
     * @param string[] $aliases
     */
    public function __construct(
        array|string $help = [],
        public array $aliases = [],
    ) {
        $this->help = ArrayHelper::wrap($help);
    }
}
