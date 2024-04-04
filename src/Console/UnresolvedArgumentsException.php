<?php

declare(strict_types=1);

namespace Tempest\Console;

use Exception;

final class UnresolvedArgumentsException extends Exception
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
        $exception->setArguments($arguments);

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
}
