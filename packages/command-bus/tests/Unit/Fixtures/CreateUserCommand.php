<?php

declare(strict_types=1);

namespace Tempest\CommandBus\Tests\Unit\Fixtures;

final readonly class CreateUserCommand
{
    public function __construct(
        public string $firstName,
        public string $lastName,
    ) {}
}
