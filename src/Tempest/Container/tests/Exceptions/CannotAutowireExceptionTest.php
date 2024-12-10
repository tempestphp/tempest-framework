<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Exceptions\CannotAutowireException;
use Tempest\Container\GenericContainer;
use Tempest\Container\Tests\Fixtures\AutowireA;

/**
 * @internal
 */
final class CannotAutowireExceptionTest extends TestCase
{
    public function test_autowire_without_exception(): void
    {
        $this->expectException(CannotAutowireException::class);

        try {
            $container = new GenericContainer();

            $container->get(AutowireA::class);
        } catch (CannotAutowireException $cannotAutowireException) {
            $this->assertStringContainsString('Cannot autowire ' . AutowireA::class . '::__construct because string cannot be resolved', $cannotAutowireException->getMessage());

            $expected = <<<'TXT'
                	┌── AutowireA::__construct(AutowireB $b)
                	├── AutowireB::__construct(AutowireC $c)
                	└── AutowireC::__construct(ContainerObjectA $other, string $unknown)
                	                                                    ▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒
                TXT;

            $this->assertStringContainsStringIgnoringLineEndings($expected, $cannotAutowireException->getMessage());
            $this->assertStringContainsString('CannotAutowireExceptionTest.php:24', $cannotAutowireException->getMessage());

            throw $cannotAutowireException;
        }
    }
}
