<?php

declare(strict_types=1);

namespace Tempest\Console;

/**
 * Defines a console command that is specifically for generating files.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class GeneratorCommand
{
    public function __construct(
        protected ?string $name = null,
        protected ?string $description = null,

        /** @var string[] */
        protected array $aliases = [],
        protected ?string $help = null,

        /** @var array<array-key, class-string<\Tempest\Console\ConsoleMiddleware>> */
        protected array $middleware = [],
        protected bool $hidden = false,

        /** @var class-string<\Tempest\Console\CompletesConsoleCommand>|null */
        protected string|null $complete = null,
    ) {}

    /**
     * Retrieve the underlying console command.
     */
    public function getConsoleCommand(): ConsoleCommand
    {
        return new ConsoleCommand(
            name       : $this->name,
            description: $this->description,
            aliases    : $this->aliases,
            help       : $this->help,
            middleware : $this->middleware,
            hidden     : $this->hidden,
            complete   : $this->complete,
        );
    }
}
