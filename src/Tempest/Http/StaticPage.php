<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;
use Tempest\Support\Reflection\MethodReflector;

#[Attribute]
final readonly class StaticPage
{
    public MethodReflector $handler;

    public function __construct(
        /** @var class-string<\Tempest\Http\DataProvider> */
        public ?string $dataProviderClass = null,
    ) {
    }

    public function setHandler(MethodReflector $handler): void
    {
        $this->handler = $handler;
    }
}
