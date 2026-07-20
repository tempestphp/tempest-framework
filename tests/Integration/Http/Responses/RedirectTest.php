<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Responses;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Status;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class RedirectTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function redirect(): void
    {
        $response = new Redirect('/to');

        $this->assertSame(Status::FOUND, $response->status);
        $this->assertSame('/to', $response->getHeader('Location')->values[0]);
    }

    #[Test]
    public function permanent(): void
    {
        $response = new Redirect('/to')->permanent();

        $this->assertSame(Status::MOVED_PERMANENTLY, $response->status);
    }
}
