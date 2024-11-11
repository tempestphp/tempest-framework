<?php

declare(strict_types=1);

namespace Tempest\Http\Json;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 */
final class JsonParserServerRequestTest extends TestCase
{
    private JsonParserServerRequest $parser;

    protected function setUp(): void
    {
        $this->parser = new JsonParserServerRequest();
    }

    public function test_parse_json_body(): void
    {
        $request = $this->givenRequest('{"test": "test"}');

        $request = ($this->parser)($request);

        $this->assertEquals(['test' => 'test'], $request->getParsedBody());
    }

    public function test_parse_empty_json_body(): void
    {
        $request = $this->givenRequest('');

        $request = ($this->parser)($request);

        $this->assertEquals([], $request->getParsedBody());
    }

    public function test_parse_invalid_json_body(): void
    {
        $request = $this->givenRequest('invalid json string');

        $request = ($this->parser)($request);

        $this->assertEquals([], $request->getParsedBody());
    }

    private function givenRequest(string $body): ServerRequestInterface
    {
        $stream = new Stream('php://temp', 'rw');
        $stream->write($body);
        $stream->rewind();

        return new ServerRequest(
            body: $stream,
            headers: ['Content-Type' => 'application/json'],
        );
    }
}
