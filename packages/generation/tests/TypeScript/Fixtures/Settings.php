<?php

namespace Tempest\Generation\Tests\TypeScript\Fixtures;

final class Settings
{
    public function __construct(
        public readonly Theme $theme,
        public readonly bool $sidebar_open,
    ) {}
}
