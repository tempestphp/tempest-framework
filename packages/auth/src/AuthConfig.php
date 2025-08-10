<?php

declare(strict_types=1);

namespace Tempest\Auth;

final class AuthConfig
{
    public function __construct(
        /** @var null|class-string<CanAuthenticate> */
        public ?string $authenticatable = null,
    ) {}
}
