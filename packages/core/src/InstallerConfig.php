<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Reflection\MethodReflector;

final class InstallerConfig
{
    /** @param array<string,InstallerDefinition> $installers */
    public function __construct(
        public array $installers = [],
    ) {}

    public function addInstaller(MethodReflector $handler, Installer $installer): self
    {
        $definition = new InstallerDefinition(
            handler: $handler,
            installer: $installer,
        );

        $this->installers[$definition->id] = $definition;

        return $this;
    }
}
