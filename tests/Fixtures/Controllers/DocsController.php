<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Controllers;

use Tempest\Router\Get;

final readonly class DocsController
{
    #[Get('/docs/{category}/{slug}')]
    public function __invoke(string $category, string $slug): void
    {
    }
}
