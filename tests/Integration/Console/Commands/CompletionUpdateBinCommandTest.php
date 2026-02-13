<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Console\CompletionRuntime;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CompletionUpdateBinCommandTest extends FrameworkIntegrationTestCase
{
    private ?string $helperBinary = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Shell completion is not supported on Windows.');
        }
    }

    protected function tearDown(): void
    {
        if ($this->helperBinary !== null && Filesystem\is_file($this->helperBinary)) {
            Filesystem\delete_file($this->helperBinary);
            $this->helperBinary = null;
        }

        parent::tearDown();
    }

    #[Test]
    public function update_bin_fails_gracefully_on_dev_version(): void
    {
        $this->helperBinary = CompletionRuntime::getHelperBinaryPath();

        $this->console
            ->call('completion:update-bin')
            ->assertError();
    }
}
