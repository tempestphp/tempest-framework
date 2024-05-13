<?php

namespace Tempest\Console\Components\Static;

use Closure;
use Tempest\Console\Components\StaticComponent;
use Tempest\Console\Console;

final readonly class StaticSearchComponent implements StaticComponent
{
    public function __construct(
        public string $label,
        public Closure $search,
    ) {
    }

    public function render(Console $console): mixed
    {
        $answer = null;

        while ($answer === null) {
            $query = $console->ask($this->label);

            $options = ($this->search)($query);

            $options = ['Search again', ...$options];

            $answer = $console->ask('Please select a result', options: $options);

            if ($answer === 'Search again') {
                $answer = null;
            }
        }
    }
}