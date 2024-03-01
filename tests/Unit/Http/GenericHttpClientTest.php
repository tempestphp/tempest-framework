<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Http;

use AidanCasey\MockClient\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Tempest\Http\GenericHttpClient;
use Tempest\Http\HttpClient;

class GenericHttpClientTest extends TestCase
{
    private HttpClient $client;
    private Client $mock;
    private Psr17Factory $factory;

    public function test_discovery_allows_for_easy_creation()
    {
        $client = new GenericHttpClient();

        $this->assertInstanceOf(GenericHttpClient::class, $client);
    }

    public function test_get_proxies_to_http_client()
    {
        $this->client->get('/test-get');

        $this
            ->mock
            ->assertMethod('GET')
            ->assertUri('/test-get');
    }

    public function test_get_with_headers_proxies_to_http_client_with_headers()
    {
        $this->client->get('/test-get-with-headers', [
            'X-Tempest' => 'We love Tempest!',
        ]);

        $this
            ->mock
            ->assertMethod('GET')
            ->assertHeaderEquals('X-Tempest', 'We love Tempest!');
    }

    public function test_head_proxies_to_http_client()
    {
        $this->client->head('/test-head');

        $this
            ->mock
            ->assertMethod('HEAD')
            ->assertUri('/test-head');
    }

    public function test_post_proxies_to_http_client()
    {
        $this->client->post(uri: '/test-post', body: '{"test":"value"}');

        $this
            ->mock
            ->assertMethod('POST')
            ->assertUri('/test-post')
            ->assertBodyIs('{"test":"value"}');
    }

    public function test_trace_proxies_to_http_client()
    {
        $this->client->trace('/test-trace');

        $this
            ->mock
            ->assertMethod('TRACE')
            ->assertUri('/test-trace');
    }

    public function test_put_proxies_to_http_client()
    {
        $this->client->put(uri: '/test-put', body: '{"test":"test-value"}');

        $this
            ->mock
            ->assertMethod('PUT')
            ->assertUri('/test-put')
            ->assertBodyIs('{"test":"test-value"}');
    }

    public function test_patch_proxies_to_http_client()
    {
        $this->client->patch(uri: '/test-patch', body: '{"firstName":"Dwight"}');

        $this
            ->mock
            ->assertMethod('PATCH')
            ->assertUri('/test-patch')
            ->assertBodyIs('{"firstName":"Dwight"}');
    }

    public function test_delete_proxies_to_http_client()
    {
        $this->client->delete(uri: '/test-delete');

        $this
            ->mock
            ->assertMethod('DELETE')
            ->assertUri('/test-delete');
    }

    public function test_options_proxies_to_http_client()
    {
        $this->client->options('/test-options');

        $this
            ->mock
            ->assertMethod('OPTIONS')
            ->assertUri('/test-options');
    }

    public function test_send_request_proxies_to_http_client()
    {
        $request = $this->factory->createRequest('POST', '/test-send-request');

        $this
            ->client
            ->sendRequest($request);

        $this
            ->mock
            ->assertMethod('POST')
            ->assertUri('/test-send-request');
    }

    public function test_sending_request_with_file_contents()
    {
        $this
            ->client
            ->post(uri: '/test-post', body: __DIR__ . '/Fixtures/test_request_body.json');

        $this
            ->mock
            ->assertMethod('POST')
            ->assertBodyIs(
                file_get_contents(__DIR__ . '/Fixtures/test_request_body.json')
            );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new Psr17Factory();

        $this->mock = new Client(
            responseFactory: $this->factory,
            streamFactory: $this->factory
        );

        $this->client = new GenericHttpClient(
            client: $this->mock,
            uriFactory: $this->factory,
            requestFactory: $this->factory,
            streamFactory: $this->factory
        );
    }
}
