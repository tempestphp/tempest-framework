<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Idempotency\Support\ProcessingOwner;
use Tempest\Idempotency\Support\ProcessingOwnerLiveness;

final class ProcessingOwnerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Idempotency tests are not supported on Windows.');
        }
    }

    #[Test]
    public function resolves_liveness_for_hosts_with_colons(): void
    {
        $liveness = new ProcessingOwner()->resolveLiveness(
            owner: '2001:db8::1|99999999|token',
            heartbeatAt: time() - 120,
            staleAfterInSeconds: 5,
        );

        $this->assertSame(ProcessingOwnerLiveness::DEAD, $liveness);
    }
}
