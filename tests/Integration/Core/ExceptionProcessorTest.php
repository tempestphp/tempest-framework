<?php

namespace Tests\Tempest\Integration\Core;

use Exception;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Exceptions\ExceptionProcessor;
use Tempest\Core\Exceptions\ExceptionsConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Http\Fixtures\NullExceptionReporter;

final class ExceptionProcessorTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->exceptions->allowProcessing();
        $this->container->get(ExceptionsConfig::class)->setReporters([NullExceptionReporter::class]);
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        NullExceptionReporter::$exceptions = [];
    }

    #[Test]
    public function exception_reporter_processes_exception_processors(): void
    {
        $processor = $this->container->get(ExceptionProcessor::class);
        $processor->process(new Exception('foo'));

        $this->assertCount(1, NullExceptionReporter::$exceptions);
        $this->assertInstanceOf(Exception::class, NullExceptionReporter::$exceptions[0]);
        $this->assertSame('foo', NullExceptionReporter::$exceptions[0]->getMessage());
    }
}
