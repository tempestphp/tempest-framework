<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Fingerprint;

use Tempest\Http\Request;

interface HttpFingerprintGenerator
{
    public function generate(Request $request): string;
}
