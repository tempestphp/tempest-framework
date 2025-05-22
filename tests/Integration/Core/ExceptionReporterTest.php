<?php

namespace Tests\Tempest\Integration\Core;

use Exception;
use Tempest\Core\AppConfig;
use Tempest\Core\ExceptionReporter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Http\Fixtures\NullExceptionProcessor;

use function Tempest\report;

final class ExceptionReporterTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->get(AppConfig::class)->exceptionProcessors = [NullExceptionProcessor::class];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        NullExceptionProcessor::$exceptions = [];
    }

    public function test_exception_reporter_processes_exception_processors(): void
    {
        /** @var ExceptionReporter $reporter */
        $reporter = $this->container->get(ExceptionReporter::class);
        $reporter->report(new Exception('foo'));

        $this->assertCount(1, NullExceptionProcessor::$exceptions);
        $this->assertInstanceOf(Exception::class, NullExceptionProcessor::$exceptions[0]);
        $this->assertSame('foo', NullExceptionProcessor::$exceptions[0]->getMessage());

        $this->assertCount(1, $reporter->reported);
        $this->assertInstanceOf(Exception::class, $reporter->reported[0]);
        $this->assertSame('foo', $reporter->reported[0]->getMessage());
    }

    public function test_report_function(): void
    {
        report(new Exception('foo'));

        $this->assertCount(1, NullExceptionProcessor::$exceptions);
        $this->assertInstanceOf(Exception::class, NullExceptionProcessor::$exceptions[0]);
        $this->assertSame('foo', NullExceptionProcessor::$exceptions[0]->getMessage());

        /** @var ExceptionReporter $reporter */
        $reporter = $this->container->get(ExceptionReporter::class);

        $this->assertCount(1, $reporter->reported);
        $this->assertInstanceOf(Exception::class, $reporter->reported[0]);
        $this->assertSame('foo', $reporter->reported[0]->getMessage());
    }
}
