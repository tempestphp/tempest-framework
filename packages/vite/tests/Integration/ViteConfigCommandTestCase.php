<?php

declare(strict_types=1);

namespace Tempest\Vite\Tests\Integration;

use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Vite\ViteConfig;
use Tempest\Vite\ViteConfigCommand;

/**
 * @internal
 */
final class ViteConfigCommandTestCase extends FrameworkIntegrationTestCase
{
    public function test_outputs_json_default_config(): void
    {
        $this->console
            ->call(ViteConfigCommand::class)
            ->assertSee('{"build_directory":"build","bridge_file_name":"vite-tempest","manifest":"manifest.json","entrypoints":[]}');
    }

    public function test_outputs_json_custom_config(): void
    {
        $this->container->config(new ViteConfig(
            buildDirectory: 'build/website',
            bridgeFileName: '.website',
            manifest: 'website.json',
            entrypoints: ['src/website/main.ts'],
        ));

        $this->console
            ->call(ViteConfigCommand::class)
            ->assertSee('{"build_directory":"build\/website","bridge_file_name":".website","manifest":"website.json","entrypoints":["src\/website\/main.ts"]}');
    }
}
