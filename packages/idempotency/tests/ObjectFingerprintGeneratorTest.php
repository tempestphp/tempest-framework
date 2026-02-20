<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Tests;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tempest\Idempotency\Fingerprint\ObjectFingerprintGenerator;

final class ObjectFingerprintGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Idempotency tests are not supported on Windows.');
        }
    }

    #[Test]
    #[RunInSeparateProcess]
    public function throws_for_circular_references(): void
    {
        $node = new CircularNode();
        $node->next = $node;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Circular reference detected while generating command fingerprint.');

        new ObjectFingerprintGenerator()->generate(new CircularCommand($node));
    }
}

final class CircularNode
{
    public ?CircularNode $next = null;
}

final readonly class CircularCommand
{
    public function __construct(
        public CircularNode $node,
    ) {}
}
