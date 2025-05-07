<?php

namespace Tests\Tempest\Integration\Http\Fixtures;

use Exception;
use Tempest\Core\HasContext;

final class ExceptionWithContext extends Exception implements HasContext
{
    public function context(): array
    {
        return [
            'foo' => 'bar',
        ];
    }
}
