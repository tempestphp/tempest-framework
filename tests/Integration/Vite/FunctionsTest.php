<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Vite;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\vite_tags;

/**
 * @internal
 */
final class FunctionsTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->vite->setRootDirectory(__DIR__ . '/Fixtures/tmp');
    }

    public function test_vite_tags(): void
    {
        $this->vite->call(
            callback: function (): void {
                $this->assertSame(
                    expected: implode('', [
                        '<script type="module" src="http://localhost:5173/@vite/client"></script>',
                        '<script type="module" src="http://localhost:5173/src/main.ts"></script>',
                    ]),
                    actual: vite_tags('src/main.ts'),
                );
            },
            files: [
                'public/vite-tempest' => ['url' => 'http://localhost:5173'],
            ],
        );
    }
}