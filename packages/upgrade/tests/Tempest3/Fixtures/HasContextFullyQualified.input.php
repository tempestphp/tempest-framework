<?php

use Tempest\Core\HasContext;

final class MyContextProvider implements HasContext
{
    public function context(): array
    {
        return [
            'user' => 'John Doe',
            'role' => 'admin',
        ];
    }
}
