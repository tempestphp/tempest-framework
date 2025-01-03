<?php

declare(strict_types=1);

namespace Tempest\Generation\Tests\Fixtures;

final class ClassWithGetPropertyHook
{
    public string $shortClosure {
        get => 'John Doe';
    }

    public string $longClosure {
        get {
            return 'John Doe';
        }
    }
}
