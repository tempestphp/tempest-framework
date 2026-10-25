<?php

declare(strict_types=1);

namespace Tempest\Idempotency;

interface CommandFingerprintGenerator
{
    public function generate(object $command): string;
}
