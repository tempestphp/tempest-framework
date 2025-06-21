<?php

namespace Tempest\Internationalization;

final class InternationalizationConfig
{
    public function __construct(
        /** @var MessageFormatFunction[] */
        public array $functions = [],
    ) {}
}
