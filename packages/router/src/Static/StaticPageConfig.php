<?php

declare(strict_types=1);

namespace Tempest\Router\Static;

use Tempest\Reflection\MethodReflector;
use Tempest\Router\StaticPage;

final class StaticPageConfig
{
    public function __construct(
        /** @var StaticPage[] $staticPages */
        public array $staticPages = [],
    ) {}

    public function addHandler(StaticPage $staticPage, MethodReflector $methodReflector): void
    {
        $staticPage->setHandler($methodReflector);

        $this->staticPages[] = $staticPage;
    }
}
