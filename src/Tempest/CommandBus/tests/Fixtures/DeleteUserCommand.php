<?php

declare(strict_types=1);

namespace Tempest\CommandBus\Tests\Fixtures;

final readonly class DeleteUserCommand
{
    public function __construct(public int $id)
    {
    }
}
