<?php

declare(strict_types=1);

namespace Tests\Tempest\Container\Exceptions;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Exceptions\CannotAutowireException;
use Tempest\Container\GenericContainer;
use Tests\Tempest\Container\Fixtures\ContainerObjectRequiringC;

class CannotAutowireExceptionTest extends TestCase
{
    /** @test */
    public function test_autowire_without_exception()
    {
        try {
            $container = new GenericContainer();

            $container->get(ContainerObjectRequiringC::class);
        } catch (CannotAutowireException $exception) {
            $this->assertStringContainsString('string $prop in ContainerObjectC::__construct()', $exception->getMessage());
            $this->assertStringContainsString('ContainerObjectC $c in ContainerObjectRequiringC::__construct()', $exception->getMessage());
            $this->assertStringContainsString('Tests\Tempest\Container\Fixtures\ContainerObjectRequiringC', $exception->getMessage());
        }
    }
}
