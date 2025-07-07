<?php

namespace Tempest\Core;

final class ExperimentalConfig
{
    public function __construct(
        /** @var \Tempest\Core\Experimental[] $experimentalFeatures */
        public array $experimentalFeatures = [],
    ) {}
}
