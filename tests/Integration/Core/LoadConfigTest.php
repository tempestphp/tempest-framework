<?php

namespace Tests\Tempest\Integration\Core;

use Tests\Tempest\Fixtures\TaggedConfigExample;
use Tests\Tempest\Integration\Core\Fixtures\HasTaggedConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class LoadConfigTest extends FrameworkIntegrationTestCase
{
    public function test_load_config(): void
    {
        $tagged1 = $this->container->get(TaggedConfigExample::class, tag: 'tagged1');
        $tagged2 = $this->container->get(TaggedConfigExample::class, tag: 'tagged2');

        $this->assertSame('tagged1', $tagged1->property);
        $this->assertSame('tagged2', $tagged2->property);

        $class = $this->container->get(HasTaggedConfig::class);

        $this->assertSame('tagged1', $class->config1->property);
        $this->assertSame('tagged2', $class->config2->property);
    }
}
