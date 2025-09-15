<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

use Tempest\Container\Inject;

final readonly class InjectA
{
    #[Inject]
    private InjectB $b; // @phpstan-ignore-line

    #[Inject('tagged')]
    private InjectB $bTagged; // @phpstan-ignore-line

    public function getB(): InjectB
    {
        return $this->b;
    }

    public function getBTagged(): InjectB
    {
        return $this->bTagged;
    }
}
