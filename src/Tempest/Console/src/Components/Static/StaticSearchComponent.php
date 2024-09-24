<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Static;

use Closure;
use Tempest\Console\Console;
use Tempest\Console\StaticConsoleComponent;

final readonly class StaticSearchComponent implements StaticConsoleComponent
{
    public const string SEARCH_AGAIN = 'Search again';

    public function __construct(
        public string $label,
        public Closure $search,
    ) {
    }

    public function render(Console $console): string
    {
        $answer = null;

        while ($answer === null) {
            $query = $console->ask($this->label);

            $options = ($this->search)($query);

            $options = [self::SEARCH_AGAIN, ...$options];

            $answer = $console->ask(
                question: 'Please select a result',
                options: $options,
                asList: true,
            );

            if ($answer === self::SEARCH_AGAIN) {
                $answer = null;
            }
        }

        return $answer;
    }
}
