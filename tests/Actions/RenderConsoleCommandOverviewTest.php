<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Actions;

use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class RenderConsoleCommandOverviewTest extends TestCase
{
    public function test_overview(): void
    {
        $this->console
            ->call('')
            ->assertContains('Tempest')
            ->assertContains('General')
            ->assertContains('Hello')
            ->assertContains('hello:world <input>')
            ->assertContains('hello:test [optionalValue=null] [--flag=false] - description')
            ->assertContains('testcommand:test');
    }
}
