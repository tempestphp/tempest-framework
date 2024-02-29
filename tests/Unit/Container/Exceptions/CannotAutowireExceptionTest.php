<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Exceptions;

use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectRequiringC;
use Throwable;

class CannotAutowireExceptionTest extends TestCase
{
    /** @test */
    public function test_autowire_without_exception()
    {
        $this->markTestSkipped('We need to implement this per #165.');

        try {
            $container = new GenericContainer();

            $container->get(ContainerObjectRequiringC::class);
            // TODO: Update the exception type.
        } catch (Throwable $exception) {
            $this->assertStringContainsString('[unresolved parameter: string $prop]', $exception->getMessage());
            $this->assertStringContainsString('Tests\Tempest\Container\Fixtures\ContainerObjectC::__construct()', $exception->getMessage());
            $this->assertStringContainsString('Tests\Tempest\Container\Fixtures\ContainerObjectRequiringC::__construct()', $exception->getMessage());
        }
    }
}
