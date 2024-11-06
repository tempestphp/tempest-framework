<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Inject;

final readonly class InjectA
{
    #[Inject]
    private InjectB $b;

    public function getB(): InjectB
    {
        return $this->b;
    }
}
