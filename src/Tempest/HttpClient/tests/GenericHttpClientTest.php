<?php

declare(strict_types=1);

namespace Tempest\HttpClient\Tests;

use AidanCasey\MockClient\Client;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\HttpClient\Driver\Psr18Driver;
use Tempest\HttpClient\GenericHttpClient;
use Tempest\HttpClient\HttpClient;

/**
 * @internal
 */
final class GenericHttpClientTest extends TestCase
{
    private HttpClient $client;

    private Client $mock;

    private HttpFactory $factory;

    public function test_send_request_proxies_to_http_client(): void
    {
        $request = new GenericRequest(method: Method::PUT, uri: '/testing-put', body: []);

        $this->client->sendRequest($request);

        $this
            ->mock
            ->assertMethod('PUT')
            ->assertUri('/testing-put');
    }

    public function test_get_proxies_to_http_client(): void
    {
        $this->client->get('/test-get');

        $this
            ->mock
            ->assertMethod('GET')
            ->assertUri('/test-get');
    }

    public function test_get_with_headers_proxies_to_http_client_with_headers(): void
    {
        $this->client->get('/test-get-with-headers', [
            'X-Tempest' => 'We love Tempest!',
        ]);

        $this
            ->mock
            ->assertMethod('GET')
            ->assertHeaderEquals('X-Tempest', 'We love Tempest!');
    }

    public function test_head_proxies_to_http_client(): void
    {
        $this->client->head('/test-head');

        $this
            ->mock
            ->assertMethod('HEAD')
            ->assertUri('/test-head');
    }

    public function test_post_proxies_to_http_client(): void
    {
        $this->client->post(uri: '/test-post', body: '{"test":"value"}');

        $this
            ->mock
            ->assertMethod('POST')
            ->assertUri('/test-post')
            ->assertBodyIs('{"test":"value"}');
    }

    public function test_trace_proxies_to_http_client(): void
    {
        $this->client->trace('/test-trace');

        $this
            ->mock
            ->assertMethod('TRACE')
            ->assertUri('/test-trace');
    }

    public function test_put_proxies_to_http_client(): void
    {
        $this->client->put(uri: '/test-put', body: '{"test":"test-value"}');

        $this
            ->mock
            ->assertMethod('PUT')
            ->assertUri('/test-put')
            ->assertBodyIs('{"test":"test-value"}');
    }

    public function test_patch_proxies_to_http_client(): void
    {
        $this->client->patch(uri: '/test-patch', body: '{"firstName":"Dwight"}');

        $this
            ->mock
            ->assertMethod('PATCH')
            ->assertUri('/test-patch')
            ->assertBodyIs('{"firstName":"Dwight"}');
    }

    public function test_delete_proxies_to_http_client(): void
    {
        $this->client->delete(uri: '/test-delete');

        $this
            ->mock
            ->assertMethod('DELETE')
            ->assertUri('/test-delete');
    }

    public function test_options_proxies_to_http_client(): void
    {
        $this->client->options('/test-options');

        $this
            ->mock
            ->assertMethod('OPTIONS')
            ->assertUri('/test-options');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new HttpFactory();

        $this->mock = new Client(
            responseFactory: $this->factory,
            streamFactory: $this->factory
        );

        $psr18Driver = new Psr18Driver(
            client: $this->mock,
            uriFactory: $this->factory,
            requestFactory: $this->factory,
            streamFactory: $this->factory
        );

        $this->client = new GenericHttpClient($psr18Driver);
    }
}
