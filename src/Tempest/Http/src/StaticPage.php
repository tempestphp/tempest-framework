<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;
use Tempest\Support\Reflection\MethodReflector;

#[Attribute]
final class StaticPage
{
    public ?MethodReflector $handler = null;

    public function __construct(
        /** @var class-string<\Tempest\Http\DataProvider> */
        public readonly ?string $dataProviderClass = null,
    ) {
    }

    public function setHandler(MethodReflector $handler): void
    {
        $this->handler = $handler;
    }
}
