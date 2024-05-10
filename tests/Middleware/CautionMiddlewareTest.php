<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Middleware;

use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class CautionMiddlewareTest extends TestCase
{
    public function test_in_local(): void
    {
        $this->console
            ->call('cautioncommand')
            ->assertContains('CAUTION confirmed');
    }

    public function test_in_production(): void
    {
        $this->markTestSkipped('Need to implement interactive testing');

        //        $appConfig = $this->container->get(AppConfig::class);
        //        $appConfig->environment = Environment::PRODUCTION;
        //
        //        $this->console
        //            ->call('cautioncommand')
        //            ->assertContains('CAUTION confirmed');
    }
}
