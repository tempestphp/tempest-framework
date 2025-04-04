<?php

namespace Tests\Tempest\Integration\Container;

use Tests\Tempest\Fixtures\Container\DependencyWithDynamicTag;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ContainerTest extends FrameworkIntegrationTestCase
{
    public function test_dynamic_tag_with_initializer(): void
    {
        $dependency = $this->container->get(DependencyWithDynamicTag::class, tag: 'bar');

        $this->assertEquals('bar', $dependency->tag);
    }

    public function test_dynamic_tag_with_initializer_without_specifying_tag(): void
    {
        $dependency = $this->container->get(DependencyWithDynamicTag::class);

        $this->assertNull($dependency->tag);
    }
}
