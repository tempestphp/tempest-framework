<?php

declare(strict_types=1);

namespace Tempest\Console\Exceptions;

use Tempest\Console\ConsoleInputArgument;
use Tempest\Console\ConsoleOutput;

final class UnresolvedArgumentsException extends ConsoleException
{
    /** @var ConsoleInputArgument[] */
    public array $arguments;

    /**
     * @param ConsoleInputArgument[] $arguments
     *
     * @return self
     */
    public static function fromArguments(array $arguments): self
    {
        $exception = new self('Unresolved arguments found');
        $exception->setArguments(array_values($arguments));

        return $exception;
    }

    /**
     * @param ConsoleInputArgument[] $arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return ConsoleInputArgument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function render(ConsoleOutput $output): void
    {
        $output->error('Unresolved arguments found');

        foreach ($this->arguments as $argument) {
            $output->writeln(
                sprintf(
                    'Argument %s is invalid',
                    $argument->getName(),
                ),
            );
        }
    }
}
