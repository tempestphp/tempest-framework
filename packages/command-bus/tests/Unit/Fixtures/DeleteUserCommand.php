<?php

declare(strict_types=1);

namespace Tempest\CommandBus\Tests\Unit\Fixtures;

final readonly class DeleteUserCommand
{
    public function __construct(
        public int $id,
    ) {}
}
