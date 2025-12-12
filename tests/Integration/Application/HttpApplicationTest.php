<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Process\Process;
use Tempest\Http\Session\Session;
use Tempest\HttpClient\HttpClient;
use Tests\Tempest\Fixtures\Controllers\ControllerWithoutSession;
use Tests\Tempest\Fixtures\Controllers\ControllerWithSession;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\Router\uri;
use function Tempest\Support\arr;

/**
 * @internal
 */
final class HttpApplicationTest extends FrameworkIntegrationTestCase
{
    public function test_http_application_run(): void
    {
        $this->http
            ->get('/')
            ->assertOk();
    }

    #[Test]
    public function session_is_not_set_even_when_it_was_cleaned_and_empty(): void
    {
        $this->appConfig->baseUri = 'http://127.0.0.1:8081';
        $process = new Process(['./tempest', 'serve', '127.0.0.1:8081'], getcwd());
        $process->start();
        usleep(200_000);

        $client = $this->get(HttpClient::class);

        $response = $client->get(uri(ControllerWithoutSession::class));
        $cookies = arr($response->getHeader('set-cookie')->values ?? [])
            ->map(fn (string $cookie) => explode('=', $cookie)[0] ?? null);

        $this->assertFalse($cookies->contains('tempest_session_id'));

        $response = $client->get(uri(ControllerWithSession::class));
        $cookies = arr($response->getHeader('set-cookie')->values ?? [])
            ->map(fn (string $cookie) => explode('=', $cookie)[0] ?? null);

        $this->assertTrue($cookies->contains('tempest_session_id'));

        $process->stop();
    }
}
