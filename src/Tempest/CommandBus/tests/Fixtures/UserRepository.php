<?php

declare(strict_types=1);

namespace Tempest\CommandBus\Tests\Fixtures;

final class UserRepository
{
    private array $users = [];

    public function addUser(string $firstName, string $lastName): void
    {
        $this->users[] = join(' ', [$firstName, $lastName]);
    }

    public function getUsers(): array
    {
        return $this->users;
    }
}
