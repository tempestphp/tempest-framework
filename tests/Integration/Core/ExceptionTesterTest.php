<?php

namespace Tests\Tempest\Integration\Core;

use Exception;
use InvalidArgumentException;
use Tempest\Core\AppConfig;
use Tempest\Core\ExceptionReporter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Http\Fixtures\NullExceptionProcessor;

use function Tempest\report;

final class ExceptionTesterTest extends FrameworkIntegrationTestCase
{
    public function test_assert_reported(): void
    {
        $this->exceptions->assertNothingReported();

        $this->container->get(ExceptionReporter::class)->report(new Exception('foo'));
        report(new Exception('bar'));

        $this->exceptions->assertReported(Exception::class, count: 2);
        $this->exceptions->assertNotReported(InvalidArgumentException::class);
    }

    public function test_prevent_reporting(): void
    {
        $this->container->get(AppConfig::class)->exceptionProcessors = [NullExceptionProcessor::class];

        $this->exceptions->preventReporting();

        $this->container->get(ExceptionReporter::class)->report(new Exception('foo'));

        $this->exceptions->assertReported(Exception::class);

        $this->assertEmpty(NullExceptionProcessor::$exceptions);
    }
}
