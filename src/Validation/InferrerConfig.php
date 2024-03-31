<?php

declare(strict_types=1);

namespace Tempest\Validation;

final class InferrerConfig
{
    public function __construct(
        /** @var Inferrer[] */
        public array $inferrers = []
    ) {

    }

    public function addInferrer(Inferrer $inferrer): self
    {
        $this->inferrers[] = $inferrer;

        return $this;
    }
}
