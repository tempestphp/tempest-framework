<?php

declare(strict_types=1);

namespace Tempest\Console;

final class PublishConfig
{
    public function __construct(
        public array $publishClasses = [],
        public array $publishFiles = []
    ) {
    }
}
