<?php

namespace Tests\Tempest\Integration\Core;

use Tempest\Core\AppConfig;
use Tempest\Core\LogExceptionProcessor;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ExceptionProcessorTest extends FrameworkIntegrationTestCase
{
    public function test_exception_processors_are_discovered(): void
    {
        $processors = $this->container->get(AppConfig::class)->exceptionProcessors;

        $this->assertContains(LogExceptionProcessor::class, $processors);
    }
}
