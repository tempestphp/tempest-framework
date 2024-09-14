<?php

declare(strict_types=1);

namespace Tempest\CommandBus\Tests\Fixtures;

use Tempest\CommandBus\CommandHandler;

final class CreateUserCommandHandler
{
    public string $firstName;

    public string $lastName;

    #[CommandHandler]
    public function __invoke(CreateUserCommand $command): void
    {
        $this->firstName = $command->firstName;
        $this->lastName = $command->lastName;
    }
}
