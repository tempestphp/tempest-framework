<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Fingerprint;

interface CommandFingerprintGenerator
{
    public function generate(object $command): string;
}
