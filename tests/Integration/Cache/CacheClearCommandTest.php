<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Cache;

use Tempest\Cache\GenericCache;
use Tests\Tempest\Fixtures\Cache\DummyCache;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CacheClearCommandTest extends FrameworkIntegrationTestCase
{
    public function test_cache_clear(): void
    {
        $this->console
            ->call('cache:clear')
            ->assertSee(GenericCache::class)
            ->assertSee(DummyCache::class)
            ->submit('0,1')
            ->submit('yes')
            ->assertSee('Tests\Tempest\Fixtures\Cache\DummyCache cleared successfully')
            ->assertSee('Tempest\Cache\GenericCache cleared successfully')
            ->assertSee('Done');
    }

    public function test_cache_clear_one_option(): void
    {
        $this->console
            ->call('cache:clear')
            ->submit('0')
            ->submit('yes')
            ->assertSee('Tests\Tempest\Fixtures\Cache\DummyCache cleared successfully')
            ->assertNotSee('Tempest\Cache\GenericCache cleared successfully')
            ->assertSee('Done');
    }

    public function test_cache_clear_all(): void
    {
        $this->console
            ->call('cache:clear --all')
            ->assertSee('Tests\Tempest\Fixtures\Cache\DummyCache cleared successfully')
            ->assertSee('Tempest\Cache\GenericCache cleared successfully')
            ->assertSee('Done');
    }
}
