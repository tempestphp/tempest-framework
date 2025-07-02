<?php

namespace Tempest\Support\Tests\Fixtures;

use Tempest\Support\HasMemoization;

final class MemoizeClass
{
    use HasMemoization;

    public int $counter = 0;

    public function do(): mixed
    {
        return $this->memoize(
            'key',
            function () {
                $this->counter += 1;
                return 'value';
            },
        );
    }
}
