<?php

declare(strict_types=1);

namespace Tempest\Console;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Parameter implements HasHelpLines
{

    private array $helpMessages = [];

    /**
     * @param array|string $helpMessages
     * @param string[] $aliases
     */
    public function __construct(
        array|string $help = [],
        public array $aliases = [],
    )
    {
        $this->helpMessages = is_array($help) ? $help : [$help];
    }

    public function getHelpLines(): array
    {
        return $this->helpMessages;
    }
}
