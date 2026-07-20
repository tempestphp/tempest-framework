<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Exceptions;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\Exceptions\DependencyCouldNotBeAutowired;
use Tempest\Container\GenericContainer;
use Tempest\Container\Tests\Fixtures\AutowireA;

/**
 * @internal
 */
final class CannotAutowireExceptionTest extends TestCase
{
    #[Test]
    public function autowire_without_exception(): void
    {
        $this->expectException(DependencyCouldNotBeAutowired::class);

        $callLine = 0;

        try {
            $container = new GenericContainer();

            $callLine = __LINE__ + 1;
            $container->get(AutowireA::class);
        } catch (DependencyCouldNotBeAutowired $cannotAutowireException) {
            $this->assertStringContainsString('Cannot autowire ' . AutowireA::class . '::__construct because string cannot be resolved', $cannotAutowireException->getMessage());

            $expected = <<<'TXT'
            	┌── AutowireA::__construct(AutowireB $b)
            	├── AutowireB::__construct(AutowireC $c)
            	└── AutowireC::__construct(ContainerObjectA $other, string $unknown)
            	                                                    ▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒
            TXT;

            $this->assertStringContainsStringIgnoringLineEndings($expected, $cannotAutowireException->getMessage());
            $this->assertStringContainsString("CannotAutowireExceptionTest.php:{$callLine}", $cannotAutowireException->getMessage());

            throw $cannotAutowireException;
        }
    }
}
