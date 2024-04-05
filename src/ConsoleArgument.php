<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;
use Tempest\Support\ArrayHelper;

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
        $this->helpMessages = ArrayHelper::wrap($help);
    }

    /** @return string[] */
    public function getHelpLines(): array
    {
        return $this->helpMessages;
    }

    /** @return string[] */
    public function getAliases(): array
    {
        return $this->aliases;
    }
}
