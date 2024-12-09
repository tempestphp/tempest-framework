<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Vite;

use Tempest\Vite\BuildConfig;
use Tempest\Vite\ViteConfig;
use Tempest\Vite\ViteConfigCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

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
            build: new BuildConfig(
                buildDirectory: 'build/website',
                bridgeFileName: '.website',
                manifest: 'website.json',
                entrypoints: ['src/website/main.ts'],
            ),
        ));

        $this->console
            ->call(ViteConfigCommand::class)
            ->assertSee('{"build_directory":"build\/website","bridge_file_name":".website","manifest":"website.json","entrypoints":["src\/website\/main.ts"]}');
    }
}
