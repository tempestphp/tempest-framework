<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Router\Responses\File;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class FileTest extends FrameworkIntegrationTestCase
{
    public function test_file(): void
    {
        $path = __DIR__ . '/Fixtures/sample.pdf';

        $response = new File($path, 'test.pdf');

        $this->assertSame(['inline; filename="test.pdf"'], $response->getHeader('Content-Disposition')->values);
        $this->assertSame('application/pdf', $response->getHeader('Content-Type')->values[0]);
        $this->assertNull($response->getHeader('Transfer-Encoding'));
        $this->assertSame((string) filesize($path), $response->getHeader('Content-Length')->values[0]);
        $this->assertSame($path, $response->body);
    }
}
