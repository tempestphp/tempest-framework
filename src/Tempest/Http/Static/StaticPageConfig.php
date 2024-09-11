<?php

declare(strict_types=1);

namespace Tempest\Http\Static;

use Tempest\Http\StaticPage;
use Tempest\Reflection\MethodReflector;

final class StaticPageConfig
{
    public function __construct(
        /** @var StaticPage[] $staticPages */
        public array $staticPages = [],
    ) {
    }

    public function addHandler(StaticPage $staticPage, MethodReflector $methodReflector): void
    {
        $staticPage->setHandler($methodReflector);

        $this->staticPages[] = $staticPage;
    }
}
