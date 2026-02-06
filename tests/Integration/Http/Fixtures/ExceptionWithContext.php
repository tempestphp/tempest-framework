<?php

namespace Tests\Tempest\Integration\Http\Fixtures;

use Exception;
use Tempest\Core\ProvidesContext;

final class ExceptionWithContext extends Exception implements ProvidesContext
{
    public function context(): array
    {
        return [
            'foo' => 'bar',
        ];
    }
}
