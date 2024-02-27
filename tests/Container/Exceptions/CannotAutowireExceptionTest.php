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
//        $this->expectException(CannotAutowireException::class);

        $container = new GenericContainer();

        $container->get(ContainerObjectRequiringC::class);
    }
}
