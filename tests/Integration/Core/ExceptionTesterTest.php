<?php

namespace Tests\Tempest\Integration\Core;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Exceptions\ExceptionProcessor;
use Tempest\Core\Exceptions\ExceptionsConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Http\Fixtures\NullExceptionReporter;

final class ExceptionTesterTest extends FrameworkIntegrationTestCase
{
    private ExceptionProcessor $processor {
        get => $this->container->get(ExceptionProcessor::class);
    }

    #[PreCondition]
    protected function configure(): void
    {
        $this->container
            ->get(ExceptionsConfig::class)
            ->setReporters([NullExceptionReporter::class]);
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        NullExceptionReporter::$exceptions = [];
    }

    #[Test]
    public function assert_reported(): void
    {
        $this->exceptions->assertNothingProcessed();

        $this->processor->process(new Exception('foo'));

        $this->exceptions->assertProcessed(Exception::class, count: 1);
        $this->exceptions->assertNotProcessed(InvalidArgumentException::class);
    }

    #[Test]
    public function prevent_reporting(): void
    {
        $this->exceptions->preventProcessing();

        $this->processor->process(new Exception('foo'));

        $this->exceptions->assertProcessed(Exception::class);
        $this->exceptions->assertProcessed(Exception::class, count: 1);

        $this->assertEmpty(NullExceptionReporter::$exceptions);
    }
}
