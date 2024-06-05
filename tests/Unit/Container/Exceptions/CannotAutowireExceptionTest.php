<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Exceptions;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Exceptions\CannotAutowireException;
use Tempest\Container\GenericContainer;
use Tests\Tempest\Unit\Container\Fixtures\AutowireA;

/**
 * @internal
 * @small
 */
class CannotAutowireExceptionTest extends TestCase
{
    public function test_autowire_without_exception()
    {
        $this->expectException(CannotAutowireException::class);

        try {
            $container = new GenericContainer();

            $container->get(AutowireA::class);
        } catch (CannotAutowireException $exception) {
            $this->assertStringContainsString('Cannot autowire Tests\Tempest\Unit\Container\Fixtures\AutowireA::__construct because string cannot be resolved', $exception->getMessage());

            $expected = <<<'TXT'
	┌── AutowireA::__construct(AutowireB $b)
	├── AutowireB::__construct(AutowireC $c)
	└── AutowireC::__construct(ContainerObjectA $other, string $unknown)
	                                                    ▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒
TXT;

            $this->assertStringContainsString($expected, $exception->getMessage());
            $this->assertStringContainsString("CannotAutowireExceptionTest.php:25", $exception->getMessage());

            throw $exception;
        }
    }
}
