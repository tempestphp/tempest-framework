<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\ORM\Caster;
use Tempest\Validation\Inferrer;

final class MapperConfig
{
    public function __construct(
        /** @var Mapper[] */
        public array $mappers = [],

        /** @var Caster[] */
        public array $casters = [],

        /** @var Inferrer[] */
        public array $inferrers = [],
    ) {

    }

    public function addMapper(Mapper $mapper): self
    {
        $this->mappers[] = $mapper;

        return $this;
    }

    public function addCaster(Caster $caster): self
    {
        $this->casters[] = $caster;

        return $this;
    }

    public function addInferrer(Inferrer $inferrer): self
    {
        $this->inferrers[] = $inferrer;

        return $this;
    }
}
