<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Static;

use Closure;
use Tempest\Console\Console;
use Tempest\Console\StaticConsoleComponent;

final readonly class StaticSearchComponent implements StaticConsoleComponent
{
    private const string SEARCH_AGAIN = 'Search again';

    private const string CANCEL = 'Cancel';

    public function __construct(
        public string $label,
        public Closure $search,
        public ?string $default = null,
    ) {
    }

    public function render(Console $console): ?string
    {
        if (! $console->supportsPrompting()) {
            return $this->default;
        }

        $answer = null;

        while ($answer === null) {
            $query = $console->ask($this->label);

            $options = ($this->search)($query);

            $options = [self::SEARCH_AGAIN, ...(count($options) === 0 ? [self::CANCEL] : []), ...$options];

            $answer = $console->ask(
                question: 'Please select a result',
                options: $options,
                asList: true,
            );

            if ($answer === self::CANCEL) {
                return $this->default;
            }

            if ($answer === self::SEARCH_AGAIN) {
                $answer = null;
            }
        }

        return $answer;
    }
}
