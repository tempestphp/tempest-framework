<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final class InvokableClass
{
    public function __invoke(): string
    {
        return 'foo';
    }

    public function execute(): string
    {
        return 'foobar';
    }
}
