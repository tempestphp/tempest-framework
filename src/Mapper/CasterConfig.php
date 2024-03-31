<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\ORM\DynamicCaster;

final class CasterConfig
{
    /**
     * @param DynamicCaster[] $casters
     */
    public function __construct(
        public array $casters = []
    ) {
    }

    public function addCaster(DynamicCaster $caster): self
    {
        $this->casters[] = $caster;

        return $this;
    }
}
