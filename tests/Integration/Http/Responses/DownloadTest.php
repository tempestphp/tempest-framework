<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use Tempest\Http\Responses\Download;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class DownloadTest extends FrameworkIntegrationTestCase
{
    public function test_download(): void
    {
        $path = __DIR__ . '/Fixtures/sample.pdf';

        $response = new Download($path, 'test.pdf');

        $this->assertSame(['attachment; filename="test.pdf"'], $response->getHeader('Content-Disposition')->values);
        $this->assertSame('application/pdf', $response->getHeader('Content-Type')->values[0]);
        $this->assertNull($response->getHeader('Transfer-Encoding'));
        $this->assertSame((string) filesize($path), $response->getHeader('Content-Length')->values[0]);
        $this->assertSame($path, $response->body);
    }
}
