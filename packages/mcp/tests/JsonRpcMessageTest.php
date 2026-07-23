<?php

declare(strict_types=1);

namespace Tempest\Mcp\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Mcp\Exceptions\RequestWasInvalid;
use Tempest\Mcp\JsonRpc\JsonRpcErrorCode;
use Tempest\Mcp\JsonRpc\JsonRpcErrorResponse;
use Tempest\Mcp\JsonRpc\JsonRpcNotification;
use Tempest\Mcp\JsonRpc\JsonRpcRequest;
use Tempest\Mcp\JsonRpc\JsonRpcSuccessResponse;

/**
 * @internal
 */
final class JsonRpcMessageTest extends TestCase
{
    #[Test]
    public function requests_round_trip(): void
    {
        $raw = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => ['name' => 'add'],
        ];

        $request = JsonRpcRequest::fromArray($raw);

        $this->assertSame(1, $request->id);
        $this->assertSame('tools/call', $request->method);
        $this->assertSame(['name' => 'add'], $request->params);
        $this->assertSame($raw, $request->toArray());
    }

    #[Test]
    public function requests_without_params_omit_them(): void
    {
        $request = JsonRpcRequest::fromArray(['jsonrpc' => '2.0', 'id' => 'a', 'method' => 'ping']);

        $this->assertSame(['jsonrpc' => '2.0', 'id' => 'a', 'method' => 'ping'], $request->toArray());
    }

    #[Test]
    public function notifications_round_trip(): void
    {
        $raw = [
            'jsonrpc' => '2.0',
            'method' => 'notifications/initialized',
            'params' => ['_meta' => ['key' => 'value']],
        ];

        $notification = JsonRpcNotification::fromArray($raw);

        $this->assertSame('notifications/initialized', $notification->method);
        $this->assertSame($raw, $notification->toArray());
    }

    #[Test]
    public function missing_jsonrpc_versions_are_rejected(): void
    {
        $this->expectException(RequestWasInvalid::class);

        JsonRpcRequest::fromArray(['id' => 1, 'method' => 'ping']);
    }

    #[Test]
    public function non_string_methods_are_rejected(): void
    {
        $this->expectException(RequestWasInvalid::class);

        JsonRpcRequest::fromArray(['jsonrpc' => '2.0', 'id' => 1, 'method' => 42]);
    }

    #[Test]
    public function invalid_ids_are_rejected(): void
    {
        $this->expectException(RequestWasInvalid::class);

        JsonRpcRequest::fromArray(['jsonrpc' => '2.0', 'id' => 1.5, 'method' => 'ping']);
    }

    #[Test]
    public function list_params_are_rejected(): void
    {
        $this->expectException(RequestWasInvalid::class);

        JsonRpcRequest::fromArray(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping', 'params' => [1, 2]]);
    }

    #[Test]
    public function success_responses_render_spec_compliant_json(): void
    {
        $response = new JsonRpcSuccessResponse(1, ['tools' => []]);

        $this->assertSame(
            '{"jsonrpc":"2.0","id":1,"result":{"tools":[]}}',
            json_encode($response->toArray()),
        );
    }

    #[Test]
    public function empty_results_serialize_as_objects(): void
    {
        $response = new JsonRpcSuccessResponse(1, []);

        $this->assertSame(
            '{"jsonrpc":"2.0","id":1,"result":{}}',
            json_encode($response->toArray()),
        );
    }

    #[Test]
    public function error_responses_render_spec_compliant_json(): void
    {
        $response = new JsonRpcErrorResponse(
            id: null,
            code: JsonRpcErrorCode::PARSE_ERROR,
            message: 'Parse error',
            data: ['detail' => 'unexpected token'],
        );

        $this->assertSame(
            '{"jsonrpc":"2.0","id":null,"error":{"code":-32700,"message":"Parse error","data":{"detail":"unexpected token"}}}',
            json_encode($response->toArray()),
        );
    }

    #[Test]
    public function error_responses_without_data_omit_the_data_key(): void
    {
        $response = new JsonRpcErrorResponse(
            id: 1,
            code: JsonRpcErrorCode::INTERNAL_ERROR,
            message: 'boom',
        );

        $this->assertSame(
            '{"jsonrpc":"2.0","id":1,"error":{"code":-32603,"message":"boom"}}',
            json_encode($response->toArray()),
        );
    }

    #[Test]
    public function notifications_without_a_method_are_rejected(): void
    {
        $this->expectException(RequestWasInvalid::class);

        JsonRpcNotification::fromArray(['jsonrpc' => '2.0']);
    }

    #[Test]
    public function error_responses_are_created_from_exceptions(): void
    {
        $response = JsonRpcErrorResponse::fromException(RequestWasInvalid::becauseBatchesAreNotSupported(), id: 3);

        $this->assertSame(3, $response->toArray()['id']);
        $this->assertSame(-32_600, $response->toArray()['error']['code']);
        $this->assertSame('Batched JSON-RPC messages are not supported.', $response->toArray()['error']['message']);
    }
}
