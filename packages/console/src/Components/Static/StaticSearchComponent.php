<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Static;

use Closure;
use Tempest\Console\Console;
use Tempest\Console\StaticConsoleComponent;

use function Tempest\Support\Arr\wrap;

final class StaticSearchComponent implements StaticConsoleComponent
{
    private const string CANCEL = 'Cancel';

    private const string SEARCH_AGAIN = 'Search again';

    public function __construct(
        public readonly string $label,
        public readonly Closure $search,
        public readonly bool $multiple = false,
        public array|string|null $default = null,
    ) {
        if ($this->multiple) {
            $this->default = wrap($this->default);
        }
    }

    public function render(Console $console): array|string|null
    {
        if (! $console->supportsPrompting()) {
            return $this->default;
        }

        do {
            $query = $console->ask($this->label);

            $options = $this->multiple
                ? ($this->search)($query)
                : [self::CANCEL, self::SEARCH_AGAIN, ...($this->search)($query)];

            $answer = $console->ask(
                question: 'Please select a result',
                options: $options,
                default: $this->multiple ? [] : self::CANCEL,
                multiple: $this->multiple,
            );

            if ($answer === self::SEARCH_AGAIN) {
                $answer = false;
            }

            if ($answer === self::CANCEL) {
                $answer = null;
            }
        } while ($answer === false);

        return $answer ?: $this->default;
    }
}
