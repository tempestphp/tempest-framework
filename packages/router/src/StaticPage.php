<?php

declare(strict_types=1);

namespace Tempest\Router;

use Attribute;
use Tempest\Reflection\MethodReflector;

#[Attribute]
final class StaticPage
{
    public ?MethodReflector $handler = null;

    public function __construct(
        /** @var class-string<\Tempest\Router\DataProvider> */
        public readonly ?string $dataProviderClass = null,
    ) {}

    public function setHandler(MethodReflector $handler): void
    {
        $this->handler = $handler;
    }
}
