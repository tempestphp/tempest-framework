<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Core\ExceptionHandler;
use Tempest\Core\FrameworkKernel;

use function Tempest\Support\Filesystem\create_directory;
use function Tempest\Support\Filesystem\delete;
use function Tempest\Support\Filesystem\exists;
use function Tempest\Support\Filesystem\write_file;

final class FrameworkKernelDiscoveryCacheFallbackTest extends TestCase
{
    private ?string $internalStorage = null;

    #[PostCondition]
    protected function cleanup(): void
    {
        putenv('ENVIRONMENT=testing');
        putenv('DISCOVERY_CACHE=true');

        restore_error_handler();
        restore_exception_handler();

        if ($this->internalStorage !== null && exists($this->internalStorage)) {
            delete($this->internalStorage);
        }

        GenericContainer::setInstance(null);
    }

    #[Test]
    public function falls_back_to_scanning_when_cached_location_is_missing(): void
    {
        putenv('ENVIRONMENT=local');
        putenv('DISCOVERY_CACHE=partial');

        $this->internalStorage = sys_get_temp_dir() . '/tempest-discovery-cache-miss-' . uniqid('', true);

        create_directory($this->internalStorage);
        write_file($this->internalStorage . '/current_discovery_strategy', 'partial');

        $kernel = FrameworkKernel::boot(
            root: dirname(__DIR__, 3),
            internalStorage: $this->internalStorage,
        );

        $this->assertInstanceOf(ExceptionHandler::class, $kernel->container->get(ExceptionHandler::class));
    }
}
