<?php

declare(strict_types=1);

namespace Tempest\Core;

final class InstallerConfig
{
    public function __construct(
        /** @var class-string<\Tempest\Core\Installer> */
        public array $installers = [],
    ) {
    }
}
