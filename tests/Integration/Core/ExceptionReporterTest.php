<?php

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\Exceptions\ExceptionsConfig;
use Tempest\Core\Exceptions\LoggingExceptionReporter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ExceptionReporterTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function logging_reporter_is_discovered(): void
    {
        $this->assertContains(LoggingExceptionReporter::class, $this->container->get(ExceptionsConfig::class)->reporters);
    }

    #[Test]
    public function logging_reporter_can_be_disabled_through_config(): void
    {
        $this->container->config(new ExceptionsConfig(logging: false));

        $this->assertEmpty($this->container->get(ExceptionsConfig::class)->reporters);
    }
}
