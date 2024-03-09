<?php

namespace Tempest\Http\Session;

final readonly class FlashValue
{
    public function __construct(
        public mixed $value,
    ) {}
}