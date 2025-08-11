<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Auth\AccessControl\Policy;
use Tempest\Auth\Authentication\CanAuthenticate;

final class AuthConfig
{
    /**
     * @param null|class-string<CanAuthenticate> $authenticatable
     * @param array<class-string<Policy>> $policies
     */
    public function __construct(
        public ?string $authenticatable = null,
        public array $policies = [],
    ) {}
}
