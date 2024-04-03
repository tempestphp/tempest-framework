<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class ConsoleArgument
{
    /** @var string[] */
    private array $helpMessages;

    /**
     * @param string|string[] $help
     * @param string[] $aliases
     */
    public function __construct(
        array|string $help = [],
        public array $aliases = [],
    ) {
        $this->helpMessages = is_array($help) ? $help : [$help];
    }

    /** @return string[] */
    public function getHelpLines(): array
    {
        return $this->helpMessages;
    }
}
