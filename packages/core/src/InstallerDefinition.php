<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Reflection\MethodReflector;

use function Tempest\Support\arr;
use function Tempest\Support\Str\to_kebab_case;

final class InstallerDefinition
{
    public function __construct(
        public MethodReflector $handler,
        public Installer $installer,
    ) {}

    /**
     * Unique identifier for this installer.
     */
    public string $id {
        get => sprintf('%s::%s', $this->handler->getDeclaringClass()->getName(), $this->handler->getName());
    }

    /**
     * Human-friendly installer name.
     */
    public string $name {
        get => $this->installer->name;
    }

    /**
     * Aliases that can be used to reference this installer.
     *
     * @var list<string>
     */
    public array $aliases {
        get => $this->aliases ??= arr($this->installer->alias)
            ->push($this->handler->getDeclaringClass()->getName())
            ->push(to_kebab_case($this->name))
            ->filter()
            ->unique()
            ->toArray();
    }
}
