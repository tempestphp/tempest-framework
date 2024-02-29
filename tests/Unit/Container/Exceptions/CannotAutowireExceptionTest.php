<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Exceptions;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Exceptions\CannotAutowireException;
use Tempest\Container\GenericContainer;
use Tests\Tempest\Unit\Container\Fixtures\AutowireA;

class CannotAutowireExceptionTest extends TestCase
{
    /** @test */
    public function test_autowire_without_exception()
    {
        $this->expectException(CannotAutowireException::class);

        try {
            $container = new GenericContainer();

            $container->get(AutowireA::class);
        } catch (CannotAutowireException $exception) {
            $this->assertStringContainsString("Cannot autowire Tests\\Tempest\\Unit\\Container\\Fixtures\\AutowireA because Tests\\Tempest\\Unit\\Container\\Fixtures\\AutowireC cannot be resolved", $exception->getMessage());

            $this->assertStringContainsString("┌── AutowireA::__construct(AutowireB \$b)", $exception->getMessage());
            $this->assertStringContainsString("├── AutowireB::__construct(AutowireC \$c)", $exception->getMessage());
            $this->assertStringContainsString("└── AutowireC::__construct(ContainerObjectA \$other, string \$unknown)", $exception->getMessage());
            $this->assertStringContainsString("                                                ▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒", $exception->getMessage());
            $this->assertStringContainsString("CannotAutowireExceptionTest.php:22", $exception->getMessage());

            throw $exception;
        }
    }
}
