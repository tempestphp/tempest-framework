<?php

namespace Tempest\Testing\Events;

interface DispatchToParentProcess
{
    public function serialize(): array;

    public static function deserialize(array $data): self;
}
