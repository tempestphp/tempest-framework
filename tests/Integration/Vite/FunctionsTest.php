<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Vite;

use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Vite\get_tags;

/**
 * @internal
 */
final class FunctionsTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->vite->setRootDirectory(__DIR__ . '/Fixtures/tmp');
    }

    #[Test]
    public function vite_tags(): void
    {
        $this->vite->call(
            callback: fn () => $this->assertSame(
                expected: [
                    '<script type="module" src="http://localhost:5173/@vite/client"></script>',
                    '<script type="module" src="http://localhost:5173/src/main.ts"></script>',
                ],
                actual: get_tags('src/main.ts'),
            ),
            files: [
                'public/vite-tempest' => ['url' => 'http://localhost:5173'],
                'src/main.ts' => '',
            ],
        );
    }
}
