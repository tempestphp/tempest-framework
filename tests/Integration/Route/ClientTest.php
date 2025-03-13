<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\Uri;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Process\Process;
use Tempest\HttpClient\HttpClient;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\root_path;

/**
 * @internal
 */
final class ClientTest extends FrameworkIntegrationTestCase
{
    private Process $server;

    private ClientInterface $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->server = new Process([root_path('tempest'), 'serve', 'localhost', '8088']);
        $this->server->start(function (...$args) {
            lw(...$args);
        });

        // Server needs to start
        usleep(100000);

        // We'll use the client interface directly because we want to write raw post data in this test
        $this->driver = $this->container->get(ClientInterface::class);
    }

    protected function tearDown(): void
    {
        if ($this->server->isRunning()) {
            $this->server->signal(SIGKILL);
        }

        while (! $this->server->isTerminated()) {
            usleep(100000);
        }

        parent::tearDown();
    }

    public function test_form_post_request(): void
    {
        $request = new RequestFactory()
            ->createRequest('POST', new Uri('http://localhost:8088/request-test/form'))
            ->withHeader('Referer', 'http://localhost:8088/request-test/form')
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody(new StreamFactory()->createStream('name=a a&b.name=b'))
        ;

        $response = $this->driver->sendRequest($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('a a', $response->getHeader('name')[0]);
        $this->assertSame('b', $response->getHeader('b.name')[0]);
    }

    public function test_json_post_request(): void
    {
        $request = new RequestFactory()
            ->createRequest('POST', new Uri('http://localhost:8088/request-test/form'))
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StreamFactory()->createStream('{"name": "a a", "b": {"name": "b"}}'))
        ;

        $response = $this->driver->sendRequest($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('a a', $response->getHeader('name')[0]);
        $this->assertSame('b', $response->getHeader('b.name')[0]);
    }
}
