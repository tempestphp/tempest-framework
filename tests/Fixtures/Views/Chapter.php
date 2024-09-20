<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Views;

final readonly class Chapter
{
    public function __construct(
        public string $title,
    ) {
    }
}
